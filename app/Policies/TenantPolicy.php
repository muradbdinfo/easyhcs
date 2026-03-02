<?php

namespace App\Policies;

use App\Models\Tenant\User;

class TenantPolicy
{
    // Used for permission-based gates.
    // Spatie's HasRoles handles permission checks via can('permission-name').
    // Add model-specific policies here as needed.

    public function before(User $user, string $ability): ?bool
    {
        // Owner and admin can do anything
        if ($user->hasRole(['tenant-owner', 'admin'])) {
            return true;
        }
        return null;
    }
}