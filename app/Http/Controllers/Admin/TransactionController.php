<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\System\PaymentTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $transactions = PaymentTransaction::with(['tenant', 'gateway'])
            ->when($request->search, fn($q, $s) =>
                $q->where('transaction_ref', 'like', "%{$s}%")
                  ->orWhereHas('tenant', fn($t) => $t->where('name', 'like', "%{$s}%"))
            )
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->gateway, fn($q, $g) => $q->where('gateway', $g))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Transactions/Index', [
            'transactions' => $transactions,
            'filters'      => $request->only('search', 'status', 'gateway'),
            'summary' => [
                'total_completed' => PaymentTransaction::where('status', 'completed')->sum('amount'),
                'this_month'      => PaymentTransaction::where('status', 'completed')
                                        ->whereMonth('created_at', now()->month)->sum('amount'),
            ],
        ]);
    }

    public function refunds(Request $request)
    {
        $refunds = PaymentTransaction::with(['tenant', 'gateway'])
            ->where('type', 'refund')
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Transactions/Refunds', [
            'refunds' => $refunds,
        ]);
    }

    public function processRefund(Request $request, PaymentTransaction $transaction)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $transaction->amount,
            'reason' => 'required|string|max:500',
        ]);

        // Create refund transaction record
        PaymentTransaction::create([
            'tenant_id'       => $transaction->tenant_id,
            'invoice_id'      => $transaction->invoice_id,
            'gateway'         => $transaction->gateway,
            'type'            => 'refund',
            'amount'          => $data['amount'],
            'currency'        => $transaction->currency,
            'status'          => 'completed',
            'transaction_ref' => 'REF-' . strtoupper(\Str::random(10)),
            'notes'           => $data['reason'],
            'refunded_from'   => $transaction->id,
        ]);

        $transaction->update(['status' => 'refunded']);

        return back()->with('success', 'Refund processed.');
    }
}