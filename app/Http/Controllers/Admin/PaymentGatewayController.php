<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\System\PaymentGateway;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentGatewayController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/Gateways/Index', [
            'gateways' => PaymentGateway::orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, PaymentGateway $gateway)
    {
        $data = $request->validate([
            'name'        => 'required|string',
            'is_active'   => 'boolean',
            'credentials' => 'required|array',
            'sort_order'  => 'integer',
        ]);
        $gateway->update($data);
        return back()->with('success', 'Gateway updated.');
    }

    public function toggle(PaymentGateway $gateway)
    {
        $gateway->update(['is_active' => !$gateway->is_active]);
        return back()->with('success', 'Gateway ' . ($gateway->is_active ? 'enabled' : 'disabled') . '.');
    }

    public function setDefault(PaymentGateway $gateway)
    {
        PaymentGateway::query()->update(['is_default' => false]);
        $gateway->update(['is_default' => true]);
        return back()->with('success', "{$gateway->name} set as default.");
    }
}