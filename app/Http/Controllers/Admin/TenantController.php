<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\System\{Tenant, Plan};
use App\Services\Admin\TenantService;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function __construct(private TenantService $service) {}

    public function index(Request $request)
    {
        return response()->json($this->service->list($request->only('search', 'status')));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'contact_email'      => 'required|email|unique:tenants,contact_email',
            'phone'      => 'nullable|string',
            'subdomain'  => 'required|string|alpha_dash',
            'plan_id'    => 'required|exists:plans,id',
            'trial_days' => 'nullable|integer|min:0|max:90',
            'address'    => 'nullable|string',
        ]);
        $tenant = $this->service->create($data);
        return response()->json($tenant, 201);
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['subscription.plan', 'domains']);
        return response()->json($tenant);
    }

    public function suspend(Tenant $tenant)
    {
        $this->service->suspend($tenant);
        return response()->json(['message' => 'Tenant suspended.']);
    }

    public function activate(Tenant $tenant)
    {
        $this->service->activate($tenant);
        return response()->json(['message' => 'Tenant activated.']);
    }

    public function destroy(Tenant $tenant)
    {
        $this->service->delete($tenant);
        return response()->json(['message' => 'Tenant deleted.']);
    }

    public function impersonate(Tenant $tenant)
    {
        session(['impersonating_tenant' => $tenant->id, 'original_admin_id' => auth()->id()]);
        return response()->json(['redirect' => "http://{$tenant->domains->first()?->domain}"]);
    }
}