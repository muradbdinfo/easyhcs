<?php

namespace App\Services;

use App\Models\Tenant\Setting;

class SettingService
{
    /**
     * Get all settings grouped.
     */
    public function all(): array
    {
        return Setting::all()
            ->groupBy('group')
            ->map(fn ($group) => $group->mapWithKeys(fn ($s) => [$s->key => $s->typed_value]))
            ->toArray();
    }

    /**
     * Bulk update settings.
     */
    public function updateMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $existing = Setting::where('key', $key)->first();
            if ($existing) {
                $stored = match ($existing->type) {
                    'json', 'array' => json_encode($value),
                    'boolean' => (int) $value,
                    default => $value,
                };
                $existing->update(['value' => $stored]);
            }
        }
    }

    /**
     * Returns flat key=>value map for frontend.
     */
    public function forFrontend(): array
    {
        return Setting::where('is_public', true)
            ->get()
            ->mapWithKeys(fn ($s) => [$s->key => $s->typed_value])
            ->toArray();
    }
}