<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Throwable;

class RobotArmClient
{
    private const COMMANDS = ['PICK', 'HOME', 'STOP', 'STATUS'];

    private const STATUSES = [
        'IDLE',
        'ACCEPTED',
        'MOVING',
        'PICKING',
        'PLACING',
        'COMPLETED',
        'ERROR',
        'STOPPED',
    ];

    public function send(string $command, ?string $orderReference = null, ?int $location = null): array
    {
        $command = strtoupper(trim($command));
        $payload = array_filter([
            'command' => $command,
            'order_id' => $orderReference,
            'location' => $location,
        ], static fn ($value) => $value !== null && $value !== '');

        if (! in_array($command, self::COMMANDS, true)) {
            return $this->localError($payload, 'INVALID_COMMAND', 'Unsupported robot command.');
        }

        if ($command === 'PICK' && ($orderReference === null || trim($orderReference) === '')) {
            return $this->localError($payload, 'INVALID_ORDER_ID', 'A PICK command requires an order ID.');
        }

        if ($command === 'PICK' && ($location === null || ! in_array($location, $this->locations(), true))) {
            return $this->localError($payload, 'INVALID_LOCATION', 'The robot location is not configured.');
        }

        if (! $this->isConfigured()) {
            return $this->localError($payload, 'ROBOT_NOT_CONFIGURED', 'Robot arm endpoint is not configured.', false);
        }

        try {
            $response = Http::acceptJson()
                ->asJson()
                ->connectTimeout($this->timeout())
                ->timeout($this->timeout())
                ->post($this->url(), $payload);
        } catch (ConnectionException $exception) {
            return [
                'ok' => false,
                'configured' => true,
                'valid_response' => false,
                'payload' => $payload,
                'data' => [
                    'status' => 'ERROR',
                    'error' => 'CONNECTION_FAILED',
                ],
                'message' => $exception->getMessage(),
            ];
        } catch (Throwable $exception) {
            return [
                'ok' => false,
                'configured' => true,
                'valid_response' => false,
                'payload' => $payload,
                'data' => [
                    'status' => 'ERROR',
                    'error' => 'COMMAND_FAILED',
                ],
                'message' => $exception->getMessage(),
            ];
        }

        $data = $response->json();
        $validResponse = is_array($data)
            && isset($data['status'])
            && is_string($data['status'])
            && in_array(strtoupper(trim($data['status'])), self::STATUSES, true);

        if (! $validResponse) {
            $data = [
                'status' => 'ERROR',
                'error' => 'INVALID_ROBOT_RESPONSE',
                'raw' => $response->body(),
            ];
        } else {
            $data['status'] = strtoupper(trim($data['status']));

            if (isset($data['error']) && ! is_scalar($data['error'])) {
                $data['error'] = 'INVALID_ROBOT_RESPONSE';
                $data['status'] = 'ERROR';
                $validResponse = false;
            }

            if ($data['status'] === 'ERROR' && empty($data['error'])) {
                $data['error'] = 'ROBOT_REPORTED_ERROR';
            }
        }

        if (! $response->successful() && empty($data['error'])) {
            $data['status'] = 'ERROR';
            $data['error'] = 'ROBOT_HTTP_ERROR';
        }

        return [
            'ok' => $response->successful(),
            'configured' => true,
            'valid_response' => $validResponse,
            'payload' => $payload,
            'data' => $data,
            'message' => $response->successful() && $validResponse
                ? 'Robot command sent.'
                : ($validResponse ? 'Robot returned HTTP '.$response->status().'.' : 'Robot returned an invalid JSON response.'),
            'http_status' => $response->status(),
        ];
    }

    public function status(): array
    {
        return $this->send('STATUS');
    }

    public function home(): array
    {
        return $this->send('HOME');
    }

    public function stop(): array
    {
        return $this->send('STOP');
    }

    public function pick(string $orderReference, int $location): array
    {
        return $this->send('PICK', $orderReference, $location);
    }

    public function isConfigured(): bool
    {
        return (bool) config('robot.enabled') && $this->baseUrl() !== '';
    }

    private function url(): string
    {
        return $this->baseUrl().'/'.ltrim((string) config('robot.command_endpoint', '/robot/command'), '/');
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('robot.base_url'), '/');
    }

    private function timeout(): int
    {
        return max(1, (int) config('robot.timeout', 5));
    }

    private function locations(): array
    {
        return array_values(array_map('intval', (array) config('robot.locations', range(1, 5))));
    }

    private function localError(array $payload, string $error, string $message, ?bool $configured = null): array
    {
        return [
            'ok' => false,
            'configured' => $configured ?? $this->isConfigured(),
            'valid_response' => false,
            'payload' => $payload,
            'data' => [
                'status' => 'ERROR',
                'error' => $error,
            ],
            'message' => $message,
        ];
    }
}
