<?php

namespace App\Services;

use App\Models\Tenant\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Send notification to a single user.
     */
    public static function send(User $user, Notification $notification): void
    {
        $user->notify($notification);
    }

    /**
     * Send notification to multiple users.
     */
    public static function sendToMany(Collection|array $users, Notification $notification): void
    {
        foreach ($users as $user) {
            $user->notify($notification);
        }
    }

    /**
     * Send notification to all users with a given role.
     */
    public static function sendToRole(string $roleName, Notification $notification): void
    {
        $users = User::role($roleName)->get();
        static::sendToMany($users, $notification);
    }

    /**
     * Send notification to all users with any of the given roles.
     */
    public static function sendToRoles(array $roleNames, Notification $notification): void
    {
        $users = User::role($roleNames)->get();
        static::sendToMany($users, $notification);
    }
}