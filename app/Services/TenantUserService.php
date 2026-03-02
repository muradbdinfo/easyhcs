<?php

namespace App\Services;

use App\Models\Tenant\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantUserService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = User::with('roles')->withTrashed(false);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%")
                  ->orWhere('phone', 'like', "%{$filters['search']}%");
            });
        }
        if (!empty($filters['role'])) {
            $query->role($filters['role']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 20);
    }

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $roles = $data['roles'] ?? [];
            unset($data['roles']);
            $data['password'] = Hash::make($data['password']);

            $user = User::create($data);
            if ($roles) {
                $user->syncRoles($roles);
            }
            return $user;
        });
    }

    public function update(User $user, array $data): User
    {
        DB::transaction(function () use ($user, $data) {
            $roles = $data['roles'] ?? null;
            unset($data['roles']);

            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);
            if ($roles !== null) {
                $user->syncRoles($roles);
            }
        });

        return $user->fresh(['roles']);
    }

    public function toggleStatus(User $user): User
    {
        $user->update([
            'status' => $user->status === 'active' ? 'inactive' : 'active',
        ]);
        return $user->fresh();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}