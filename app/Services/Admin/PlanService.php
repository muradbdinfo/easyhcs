<?php

namespace App\Services\Admin;

use App\Models\System\Plan;

class PlanService
{
    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return Plan::withCount('subscriptions')->orderBy('sort_order')->get();
    }

    public function create(array $data): Plan
    {
        return Plan::create([
            'name'          => $data['name'],
            'slug'          => \Str::slug($data['name']),
            'description'   => $data['description'] ?? null,
            'price_monthly' => $data['price_monthly'],
            'price_yearly'  => $data['price_yearly'],
            'modules'       => $data['modules'],
            'limits'        => $data['limits'] ?? [],
            'is_active'     => $data['is_active'] ?? true,
            'sort_order'    => $data['sort_order'] ?? 0,
        ]);
    }

    public function update(Plan $plan, array $data): Plan
    {
        $plan->update($data);
        return $plan->fresh();
    }

    public function delete(Plan $plan): void
    {
        if ($plan->subscriptions()->exists()) {
            throw new \Exception('Cannot delete plan with active subscriptions.');
        }
        $plan->delete();
    }
}