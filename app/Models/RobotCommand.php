<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RobotCommand extends Model
{
    use HasFactory, HasPublicId;

    protected $table = 'robot_arm_commands';

    public const STATUS_IDLE = 'IDLE';

    public const STATUS_PENDING = 'PENDING';

    public const STATUS_ACCEPTED = 'ACCEPTED';

    public const STATUS_MOVING = 'MOVING';

    public const STATUS_PICKING = 'PICKING';

    public const STATUS_PLACING = 'PLACING';

    public const STATUS_COMPLETED = 'COMPLETED';

    public const STATUS_ERROR = 'ERROR';

    public const STATUS_STOPPED = 'STOPPED';

    protected $fillable = [
        'public_id',
        'order_id',
        'order_reference',
        'command',
        'location',
        'status',
        'error',
        'request_payload',
        'response_payload',
        'last_polled_at',
        'completed_at',
        'failed_at',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'last_polled_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function markFromRobotResponse(array $response): void
    {
        $status = strtoupper(trim((string) ($response['status'] ?? $this->status)));

        if (! in_array($status, self::validStatuses(), true)) {
            $status = self::STATUS_ERROR;
            $response = [
                'status' => self::STATUS_ERROR,
                'error' => 'INVALID_ROBOT_RESPONSE',
                'response' => $response,
            ];
        }

        if (! $this->canTransitionTo($status)) {
            $this->last_polled_at = now();
            $this->save();

            return;
        }

        $this->status = $status;
        $this->response_payload = $response;
        $this->last_polled_at = now();
        $error = $response['error'] ?? null;
        $this->error = $error === null
            ? null
            : (is_scalar($error) ? mb_substr((string) $error, 0, 255) : 'INVALID_ROBOT_RESPONSE');

        if ($status === self::STATUS_COMPLETED) {
            $this->completed_at ??= now();
            $this->failed_at = null;
        }

        if (in_array($status, [self::STATUS_ERROR, self::STATUS_STOPPED], true)) {
            $this->failed_at = now();
            $this->completed_at = null;
        }

        if (in_array($status, self::activeStatuses(), true)) {
            $this->completed_at = null;
            $this->failed_at = null;
        }

        $this->save();
    }

    public function getIsActiveAttribute(): bool
    {
        return in_array($this->status, self::activeStatuses(), true);
    }

    public static function activeStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_ACCEPTED,
            self::STATUS_MOVING,
            self::STATUS_PICKING,
            self::STATUS_PLACING,
        ];
    }

    public static function validStatuses(): array
    {
        return [
            self::STATUS_IDLE,
            ...self::activeStatuses(),
            self::STATUS_COMPLETED,
            self::STATUS_ERROR,
            self::STATUS_STOPPED,
        ];
    }

    private function canTransitionTo(string $nextStatus): bool
    {
        $currentStatus = strtoupper((string) $this->status);

        if ($currentStatus === $nextStatus || $currentStatus === '') {
            return true;
        }

        if (in_array($currentStatus, [self::STATUS_COMPLETED, self::STATUS_ERROR, self::STATUS_STOPPED], true)) {
            return false;
        }

        if (in_array($nextStatus, [self::STATUS_COMPLETED, self::STATUS_ERROR, self::STATUS_STOPPED], true)) {
            return true;
        }

        if ($nextStatus === self::STATUS_IDLE) {
            return $currentStatus === self::STATUS_PENDING;
        }

        $progress = array_flip(self::activeStatuses());

        return isset($progress[$currentStatus], $progress[$nextStatus])
            && $progress[$nextStatus] >= $progress[$currentStatus];
    }
}
