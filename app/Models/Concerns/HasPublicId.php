<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

trait HasPublicId
{
    protected static function bootHasPublicId(): void
    {
        static::creating(function ($model): void {
            if (empty($model->public_id)) {
                $model->public_id = (string) Str::uuid();
            }
        });
    }

    public function scopeWherePublicIdOrId(Builder $query, string|int $value): Builder
    {
        return $query->where('public_id', $value);
    }

    public static function findByPublicIdOrFail(string|int $value): self
    {
        return static::query()->wherePublicIdOrId($value)->firstOrFail();
    }

    public static function findByPublicId(string|int $value): ?self
    {
        return static::query()->wherePublicIdOrId($value)->first();
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }
}
