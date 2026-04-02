<?php

namespace App\Traits;

use App\Models\AuditLog;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            static::logAudit($model, 'created', [], $model->getAttributes());
        });

        static::updated(function ($model) {
            $original = $model->getOriginal();
            $changes = $model->getChanges();
            static::logAudit($model, 'updated', array_intersect_key($original, $changes), $changes);
        });

        static::deleted(function ($model) {
            static::logAudit($model, 'deleted', $model->getAttributes(), []);
        });
    }

    protected static function logAudit($model, string $action, array $oldValues, array $newValues): void
    {
        $user = auth()->user();

        AuditLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
