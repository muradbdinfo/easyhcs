<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Services\AuditService;
use App\Services\TenantUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct(private TenantUserService $service) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('manage-users');

        $users = $this->service->list($request->only(['search', 'role', 'status', 'per_page']));
        $roles = Role::orderBy('name')->get(['id', 'name']);

        return response()->json(compact('users', 'roles'));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('manage-users');

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'status'   => ['required', 'in:active,inactive'],
            'roles'    => ['required', 'array', 'min:1'],
            'roles.*'  => ['string', 'exists:roles,name'],
        ]);

        $user = $this->service->create($data);
        AuditService::log('created', $user, null, $user->only(['name', 'email', 'status']), "User '{$user->name}' created");

        return response()->json(['message' => 'User created successfully.', 'user' => $user->load('roles')], 201);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('manage-users');
        return response()->json(['user' => $user->load('roles')->append('avatar_url')]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorize('manage-users');

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', "unique:users,email,{$user->id}"],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status'   => ['required', 'in:active,inactive,suspended'],
            'roles'    => ['required', 'array', 'min:1'],
            'roles.*'  => ['string', 'exists:roles,name'],
        ]);

        $old     = $user->only(['name', 'email', 'status']);
        $updated = $this->service->update($user, $data);
        AuditService::log('updated', $updated, $old, $updated->only(['name', 'email', 'status']), "User '{$updated->name}' updated");

        return response()->json(['message' => 'User updated successfully.', 'user' => $updated->load('roles')]);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('manage-users');

        if ($user->hasRole('tenant-owner')) {
            return response()->json(['message' => 'Cannot delete the tenant owner.'], 422);
        }

        AuditService::log('deleted', $user, $user->only(['name', 'email']), null, "User '{$user->name}' deleted");
        $this->service->delete($user);

        return response()->json(['message' => 'User deleted successfully.']);
    }

    public function toggleStatus(User $user): JsonResponse
    {
        $this->authorize('manage-users');

        if ($user->hasRole('tenant-owner')) {
            return response()->json(['message' => 'Cannot change status of tenant owner.'], 422);
        }

        $updated = $this->service->toggleStatus($user);
        AuditService::log('updated', $updated, ['status' => $user->status], ['status' => $updated->status]);

        return response()->json(['message' => 'Status updated.', 'user' => $updated]);
    }
}