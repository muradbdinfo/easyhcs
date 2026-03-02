<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::guard('tenant')->attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::guard('tenant')->user();

        if ($user->status !== 'active') {
            Auth::guard('tenant')->logout();
            throw ValidationException::withMessages([
                'email' => ['Your account has been suspended. Please contact the administrator.'],
            ]);
        }

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        AuditService::log('login', $user, null, null, 'User logged in');

        $request->session()->regenerate();

        return response()->json([
            'user'        => $user->load('roles:id,name')->append('avatar_url'),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'roles'       => $user->getRoleNames(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = Auth::guard('tenant')->user();
        if ($user) {
            AuditService::log('logout', $user, null, null, 'User logged out');
        }

        Auth::guard('tenant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(): JsonResponse
    {
        $user = Auth::guard('tenant')->user();
        return response()->json([
            'user'        => $user->load('roles:id,name')->append('avatar_url'),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'roles'       => $user->getRoleNames(),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = Auth::guard('tenant')->user();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $old = $user->only(['name', 'phone']);
        $user->update($data);
        AuditService::log('updated', $user, $old, $data, 'Profile updated');

        return response()->json(['user' => $user->fresh()->append('avatar_url')]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password:tenant'],
            'password'         => ['required', 'min:8', 'confirmed'],
        ]);

        $user = Auth::guard('tenant')->user();
        $user->update(['password' => bcrypt($request->password)]);
        AuditService::log('updated', $user, null, null, 'Password changed');

        return response()->json(['message' => 'Password updated successfully.']);
    }
}