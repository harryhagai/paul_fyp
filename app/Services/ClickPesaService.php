<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ClickPesaService
{
    public function initiateUssdPush(Order $order, ?string $orderReference = null): array
    {
        $payload = [
            'amount' => (string) number_format((float) $order->total_amount, 2, '.', ''),
            'currency' => $order->currency ?: 'TZS',
            'orderReference' => $orderReference ?: $this->orderReference($order),
            'phoneNumber' => $this->normalizePhone($order->customer_phone ?: $order->user?->phone_number),
        ];

        if (config('clickpesa.use_checksum')) {
            $payload['checksum'] = $this->checksum($payload);
        }

        $response = $this->http()
            ->withHeaders([
                'Authorization' => $this->authorizationHeader(),
            ])
            ->post($this->url('payments/initiate-ussd-push-request'), $payload);

        if (!$response->successful()) {
            throw new RuntimeException('ClickPesa USSD push failed: ' . $response->body());
        }

        return $response->json();
    }

    public function queryPaymentStatus(string $orderReference): array
    {
        $safeReference = preg_replace('/[^A-Za-z0-9]/', '', $orderReference);

        $response = $this->http()
            ->withHeaders([
                'Authorization' => $this->authorizationHeader(),
            ])
            ->get($this->url('payments/' . rawurlencode($safeReference)));

        if (!$response->successful()) {
            throw new RuntimeException('ClickPesa payment status query failed: ' . $response->body());
        }

        return $response->json();
    }

    public function validChecksum(array $payload): bool
    {
        $received = Arr::get($payload, 'checksum');
        $key = config('clickpesa.checksum_key');

        if (!$received || !$key) {
            return false;
        }

        $cleanPayload = $payload;
        unset($cleanPayload['checksum'], $cleanPayload['checksumMethod']);

        return hash_equals($received, $this->checksum($cleanPayload));
    }

    public function orderReference(Order $order): string
    {
        return preg_replace('/[^A-Za-z0-9]/', '', (string) $order->order_number);
    }

    private function token(): string
    {
        $clientId = config('clickpesa.client_id');
        $apiKey = config('clickpesa.api_key');

        if (!$clientId || !$apiKey) {
            throw new RuntimeException('ClickPesa credentials are not configured.');
        }

        return Cache::remember($this->tokenCacheKey(), now()->addMinutes(55), function () use ($clientId, $apiKey) {
            $response = $this->http()
                ->withHeaders([
                    'client-id' => $clientId,
                    'api-key' => $apiKey,
                ])
                ->post($this->url('generate-token'));

            if (!$response->successful() || !$response->json('token')) {
                throw new RuntimeException('ClickPesa token failed: ' . $response->body());
            }

            return $response->json('token');
        });
    }

    private function authorizationHeader(): string
    {
        $token = $this->token();

        return str_starts_with(strtolower($token), 'bearer ')
            ? $token
            : 'Bearer ' . $token;
    }

    private function checksum(array $payload): string
    {
        if (!config('clickpesa.checksum_key')) {
            throw new RuntimeException('ClickPesa checksum is enabled, but CLICKPESA_CHECKSUM_KEY is not configured.');
        }

        $canonical = $this->canonicalize($payload);
        $json = json_encode($canonical, JSON_UNESCAPED_SLASHES);

        return hash_hmac('sha256', $json, config('clickpesa.checksum_key'));
    }

    private function canonicalize(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(fn ($item) => $this->canonicalize($item), $value);
        }

        ksort($value);

        foreach ($value as $key => $item) {
            $value[$key] = $this->canonicalize($item);
        }

        return $value;
    }

    private function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            return '255' . substr($digits, 1);
        }

        if (strlen($digits) === 9) {
            return '255' . $digits;
        }

        return $digits ?: null;
    }

    private function tokenCacheKey(): string
    {
        $fingerprint = hash('sha256', implode('|', [
            config('clickpesa.client_id'),
            config('clickpesa.api_key'),
            config('clickpesa.use_checksum') ? 'checksum-on' : 'checksum-off',
            config('clickpesa.checksum_key') ?: 'no-checksum-key',
        ]));

        return 'clickpesa.authorization_token.' . $fingerprint;
    }

    private function url(string $path): string
    {
        return config('clickpesa.base_url') . '/' . ltrim($path, '/');
    }

    private function http(): PendingRequest
    {
        return Http::acceptJson()
            ->withOptions([
                'verify' => (bool) config('clickpesa.verify_ssl', true),
            ]);
    }
}
