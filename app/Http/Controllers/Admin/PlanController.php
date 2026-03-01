<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\System\Plan;
use App\Services\Admin\PlanService;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function __construct(private PlanService $service) {}

    public function index()
    {
        return response()->json($this->service->all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'description'   => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly'  => 'required|numeric|min:0',
            'modules'       => 'required|array',
            'modules.*'     => 'in:core,pharmacy,diagnostic,hospital,accounts',
            'is_active'     => 'boolean',
            'sort_order'    => 'integer',
        ]);

        // map frontend 'modules' → DB 'modules_enabled'
        $data['modules_enabled'] = $data['modules'];
        unset($data['modules']);

        $plan = $this->service->create($data);
        return response()->json($plan, 201);
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'description'   => 'nullable|string',
            'price_monthly' => 'required|numeric|min:0',
            'price_yearly'  => 'required|numeric|min:0',
            'modules'       => 'required|array',
            'is_active'     => 'boolean',
            'sort_order'    => 'integer',
        ]);

        $data['modules_enabled'] = $data['modules'];
        unset($data['modules']);

        $this->service->update($plan, $data);
        return response()->json(['message' => 'Plan updated.']);
    }

    public function destroy(Plan $plan)
    {
        try {
            $this->service->delete($plan);
            return response()->json(['message' => 'Plan deleted.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}