<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\System\Subscription;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
   public function index(Request $request)
{
    $subs = Subscription::with(['tenant', 'plan'])
        ->when($request->search, fn($q, $s) =>
            $q->whereHas('tenant', fn($q) =>
                $q->where('business_name', 'like', "%{$s}%")
            )
        )
        ->when($request->status, fn($q, $s) => $q->where('status', $s))
        ->latest()
        ->paginate(20);

    return response()->json($subs);
}

    public function show(Subscription $subscription)
    {
        $subscription->load(['tenant', 'plan', 'invoices.transactions']);

        return Inertia::render('Admin/Subscriptions/Show', [
            'subscription' => $subscription,
        ]);
    }

    public function cancel(Subscription $subscription)
    {
        $subscription->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        $subscription->tenant->update(['status' => 'suspended']);
        return back()->with('success', 'Subscription cancelled.');
    }
}