<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\System\License;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class LicenseController extends Controller
{
    public function index(Request $request)
    {
        $licenses = License::with('tenant')
            ->when($request->search, fn($q, $s) =>
                $q->where('key', 'like', "%{$s}%")
                  ->orWhereHas('tenant', fn($t) => $t->where('name', 'like', "%{$s}%"))
            )
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Licenses/Index', [
            'licenses' => $licenses,
            'filters'  => $request->only('search', 'status'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tenant_id'  => 'required|exists:tenants,id',
            'expires_at' => 'nullable|date',
            'max_users'  => 'nullable|integer',
        ]);

        License::create([
            ...$data,
            'key'    => 'LIC-' . strtoupper(Str::random(20)),
            'status' => 'active',
        ]);

        return back()->with('success', 'License key generated.');
    }

    public function revoke(License $license)
    {
        $license->update(['status' => 'revoked', 'revoked_at' => now()]);
        return back()->with('success', 'License revoked.');
    }
}