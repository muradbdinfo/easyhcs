<?php
namespace App\Services\Admin;

use App\Models\System\Tenant;
use App\Models\System\Plan;
use App\Models\System\Subscription;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TenantService
{
    public function list(array $filters = [])
    {
        return Tenant::with(['subscription.plan'])
            ->when($filters['search'] ?? null, fn($q, $s) =>
                $q->where('business_name', 'like', "%{$s}%")
                  ->orWhere('contact_email', 'like', "%{$s}%")
            )
            ->when($filters['status'] ?? null, fn($q, $s) =>
                $q->where('plan_status', $s)
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();
    }

    public function create(array $data): Tenant
    {
        return DB::transaction(function () use ($data) {
            $tenant = Tenant::create([
                'id'             => Str::slug($data['subdomain']),
                'business_name'  => $data['name'],
                'contact_email'  => $data['contact_email'],
                'contact_phone'  => $data['phone'] ?? null,
                'address'        => $data['address'] ?? null,
                'plan_id'        => $data['plan_id'],
                'plan_status'    => 'trialing',
                'is_active'      => true,
                'trial_ends_at'  => Carbon::now()->addDays($data['trial_days'] ?? 14),
                'plan_expires_at'=> Carbon::now()->addDays($data['trial_days'] ?? 14),
            ]);

            $tenant->domains()->create(['domain' => $data['subdomain'] . '.easyhcs.local']);

            $plan = Plan::findOrFail($data['plan_id']);
            Subscription::create([
                'tenant_id'     => $tenant->id,
                'plan_id'       => $plan->id,
                'status'        => 'trialing',
                'starts_at'     => now(),
                'ends_at'       => Carbon::now()->addDays($data['trial_days'] ?? 14),
                'billing_cycle' => 'monthly',
                'amount'        => $plan->price_monthly,
                'currency'      => 'BDT',
            ]);

            \Artisan::call('tenants:migrate', [
                '--tenants' => [$tenant->id],
                '--force'   => true,
            ]);

            return $tenant->fresh(['subscription.plan', 'domains']);
        });
    }

    public function suspend(Tenant $tenant): void
    {
        $tenant->update(['plan_status' => 'suspended', 'is_active' => false]);
        $tenant->subscription?->update(['status' => 'suspended']);
    }

    public function activate(Tenant $tenant): void
    {
        $tenant->update(['plan_status' => 'active', 'is_active' => true]);
        $tenant->subscription?->update(['status' => 'active']);
    }

    public function delete(Tenant $tenant): void
    {
        DB::transaction(function () use ($tenant) {
            $tenant->subscription?->delete();
            $tenant->domains()->delete();
            $tenant->delete();
        });
    }

    public function getStats(): array
    {
        return [
            'total'          => Tenant::count(),
            'active'         => Tenant::where('is_active', true)->count(),
            'suspended'      => Tenant::where('plan_status', 'suspended')->count(),
            'trial'          => Tenant::where('plan_status', 'trialing')->count(),
            'new_this_month' => Tenant::whereMonth('created_at', now()->month)->count(),
        ];
    }
}