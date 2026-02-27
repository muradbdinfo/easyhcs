<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    public function __construct(private TwoFactorService $twoFactorService) {}

    /**
     * POST /two-factor-challenge
     * Verify the OTP submitted after login.
     */
    public function challenge(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! $this->twoFactorService->verify($user, $request->code)) {
            throw ValidationException::withMessages([
                'code' => 'The verification code is invalid or has expired.',
            ]);
        }

        $request->session()->forget('two_factor_pending');
        $request->session()->regenerate();

        $redirect = $user->is_super_admin ? '/admin/dashboard' : '/dashboard';

        return response()->json([
            'user'     => [
                'id'             => $user->id,
                'name'           => $user->name,
                'email'          => $user->email,
                'is_super_admin' => $user->is_super_admin,
            ],
            'redirect' => $redirect,
        ]);
    }

    /**
     * POST /two-factor-challenge/resend
     */
    public function resend(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $this->twoFactorService->send($user);

        return response()->json(['message' => 'A new verification code has been sent.']);
    }

    /**
     * POST /user/two-factor-authentication  — Enable 2FA
     */
    public function enable(Request $request): JsonResponse
    {
        $this->twoFactorService->enable($request->user());

        return response()->json(['message' => 'Two-factor authentication enabled.']);
    }

    /**
     * DELETE /user/two-factor-authentication  — Disable 2FA
     */
    public function disable(Request $request): JsonResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);

        $this->twoFactorService->disable($request->user());

        return response()->json(['message' => 'Two-factor authentication disabled.']);
    }
}