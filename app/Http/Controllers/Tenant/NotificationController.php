<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = Auth::guard('tenant')->user();
        $query = $user->notifications();

        if ($request->boolean('unread_only')) {
            $query->whereNull('read_at');
        }

        $notifications = $query->latest()->paginate($request->integer('per_page', 20));
        $unread_count  = $user->unreadNotifications()->count();

        return response()->json(compact('notifications', 'unread_count'));
    }

    public function markAsRead(string $id): JsonResponse
    {
        $user         = Auth::guard('tenant')->user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function markAllRead(): JsonResponse
    {
        Auth::guard('tenant')->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'All notifications marked as read.']);
    }

    public function destroy(string $id): JsonResponse
    {
        $user = Auth::guard('tenant')->user();
        $user->notifications()->findOrFail($id)->delete();
        return response()->json(['message' => 'Notification deleted.']);
    }

    public function unreadCount(): JsonResponse
    {
        $count = Auth::guard('tenant')->user()->unreadNotifications()->count();
        return response()->json(compact('count'));
    }
}