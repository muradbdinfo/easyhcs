<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function __construct(private TwoFactorService $twoFactorService) {}

    /**
     * POST /login
     */
    public function store(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = (bool) $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        $user = Auth::user();

        // Check if account is active
        if (isset($user->is_active) && ! $user->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'Your account has been deactivated. Contact your administrator.',
            ]);
        }

        // 2FA check
        if ($user->two_factor_enabled) {
            // Send OTP and mark session as pending 2FA
            $this->twoFactorService->send($user);

            // Store pending state in session (user is auth'd but 2FA not confirmed)
            $request->session()->put('two_factor_pending', true);

            return response()->json([
                'two_factor_required' => true,
                'message'             => 'Verification code sent to your email.',
            ]);
        }

        $request->session()->regenerate();

        return response()->json([
            'two_factor_required' => false,
            'user'                => $this->userData($user),
            'redirect'            => $this->redirectPath($user),
        ]);
    }

    /**
     * DELETE /logout
     */
    public function destroy(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    // ─────────────────────────────────────────────────────────────

    private function redirectPath($user): string
    {
        if ($user->is_super_admin) {
            return '/admin/dashboard';
        }

        return '/dashboard';
    }

    private function userData($user): array
    {
        return [
            'id'              => $user->id,
            'name'            => $user->name,
            'email'           => $user->email,
            'is_super_admin'  => $user->is_super_admin,
            'two_factor_enabled' => $user->two_factor_enabled,
        ];
    }
}