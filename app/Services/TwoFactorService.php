<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Support\Facades\Cache;

class TwoFactorService
{
    /**
     * Generate a fresh OTP, persist it on the user, and send it.
     */
    public function send(User $user): void
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->forceFill([
            'two_factor_code'       => $code,
            'two_factor_expires_at' => now()->addMinutes(10),
        ])->save();

        $user->notify(new TwoFactorCodeNotification($code));
    }

    /**
     * Verify submitted code against stored code.
     */
    public function verify(User $user, string $code): bool
    {
        if (
            $user->two_factor_code === $code &&
            $user->two_factor_expires_at &&
            now()->lessThanOrEqualTo($user->two_factor_expires_at)
        ) {
            // Clear used code
            $user->forceFill([
                'two_factor_code'       => null,
                'two_factor_expires_at' => null,
            ])->save();

            return true;
        }

        return false;
    }

    /**
     * Enable 2FA for a user.
     */
    public function enable(User $user): void
    {
        $user->forceFill(['two_factor_enabled' => true])->save();
    }

    /**
     * Disable 2FA for a user.
     */
    public function disable(User $user): void
    {
        $user->forceFill([
            'two_factor_enabled'    => false,
            'two_factor_code'       => null,
            'two_factor_expires_at' => null,
        ])->save();
    }
}