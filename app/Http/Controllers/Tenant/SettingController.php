<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\AuditService;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(private SettingService $service) {}

    public function index(): JsonResponse
    {
        $this->authorize('manage-settings');
        return response()->json(['settings' => $this->service->all()]);
    }

    public function update(Request $request): JsonResponse
    {
        $this->authorize('manage-settings');

        $data = $request->validate([
            'settings'                     => ['required', 'array'],
            'settings.clinic_name'         => ['sometimes', 'string', 'max:150'],
            'settings.clinic_address'      => ['sometimes', 'string'],
            'settings.clinic_phone'        => ['sometimes', 'string', 'max:20'],
            'settings.clinic_email'        => ['sometimes', 'email'],
            'settings.currency'            => ['sometimes', 'string', 'size:3'],
            'settings.timezone'            => ['sometimes', 'timezone'],
            'settings.date_format'         => ['sometimes', 'string'],
            'settings.modules_enabled'     => ['sometimes', 'array'],
            'settings.modules_enabled.*'   => ['string', 'in:pharmacy,diagnostic,hospital,accounts'],
            'settings.payment_methods'     => ['sometimes', 'array'],
            'settings.payment_methods.*'   => ['string'],
            'settings.logo'                => ['sometimes', 'nullable', 'string'],
        ]);

        $this->service->updateMany($data['settings']);
        AuditService::log('updated', null, null, $data['settings'], 'Tenant settings updated');

        return response()->json(['message' => 'Settings saved successfully.']);
    }

    public function uploadLogo(Request $request): JsonResponse
    {
        $this->authorize('manage-settings');
        $request->validate(['logo' => ['required', 'image', 'mimes:png,jpg,jpeg,svg', 'max:2048']]);

        $path = $request->file('logo')->store('logos', 'public');
        \App\Models\Tenant\Setting::set('logo', $path, 'string', 'general');

        AuditService::log('updated', null, null, ['logo' => $path], 'Clinic logo updated');

        return response()->json(['message' => 'Logo uploaded.', 'path' => asset('storage/' . $path)]);
    }
}