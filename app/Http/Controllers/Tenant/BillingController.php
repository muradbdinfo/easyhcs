<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\System\Subscription;
use App\Models\System\SubscriptionInvoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    public function index(): JsonResponse
    {
        $tenantId = tenant('id');

        // Query system DB for subscription info
        $subscription = Subscription::where('tenant_id', $tenantId)
                                     ->with('plan')
                                     ->latest()
                                     ->first();

        $invoices = SubscriptionInvoice::where('tenant_id', $tenantId)
                                        ->latest()
                                        ->take(12)
                                        ->get();

        return response()->json([
            'subscription' => $subscription,
            'invoices'     => $invoices,
        ]);
    }
}