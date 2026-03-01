<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class SettingsController extends Controller
{
    private array $settingKeys = [
        'app_name', 'app_logo', 'support_email', 'support_phone',
        'default_currency', 'default_timezone',
        'smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass',
        'sms_provider', 'sms_api_key', 'sms_from',
        'maintenance_mode', 'registration_open',
        'default_trial_days', 'invoice_prefix',
    ];

    public function index()
    {
        $settings = collect($this->settingKeys)->mapWithKeys(fn($k) => [
            $k => config("system.{$k}", \DB::table('system_settings')->where('key', $k)->value('value'))
        ]);

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($data['settings'] as $key => $value) {
            if (in_array($key, $this->settingKeys)) {
                \DB::table('system_settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $value, 'updated_at' => now()]
                );
            }
        }

        Cache::forget('system_settings');

        return back()->with('success', 'Settings saved.');
    }
}