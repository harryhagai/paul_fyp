<?php

namespace App\Http\Controllers\seller;

use App\Http\Controllers\Controller;
use App\Models\SellerSettingPermission;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $permissions = SellerSettingPermission::orderBy('group')->orderBy('setting_key')->get();

        $allowedKeys = $permissions->where('can_view', true)->pluck('setting_key');

        $settings = SiteSetting::whereIn('key', $allowedKeys)->orderBy('group')->orderBy('key')->get()->keyBy('key');

        return view('seller.settings', compact('permissions', 'settings'));
    }

    public function upsert(Request $request)
    {
        $permissions = SellerSettingPermission::all()->keyBy('setting_key');

        $validated = $request->validate([
            'settings' => 'nullable|array',
            'settings.*.key' => 'required|string|max:191',
            'settings.*.value' => 'nullable|string',
            'settings.*.group' => 'nullable|string|max:100',
        ]);

        foreach (($validated['settings'] ?? []) as $settingData) {
            $key = $settingData['key'];
            $permission = $permissions->get($key);

            if (!$permission || !$permission->can_update) {
                continue;
            }

            SiteSetting::updateOrCreate(
                ['key' => $key],
                [
                    'group' => $settingData['group'] ?: $permission->group,
                    'value' => $settingData['value'] ?? '',
                ]
            );
        }

        return back()->with('success', 'Settings updated successfully.');
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'key' => 'required|string|max:191',
            'value' => 'nullable|string',
            'group' => 'nullable|string|max:100',
        ]);

        $permission = SellerSettingPermission::where('setting_key', $data['key'])->first();
        if (!$permission || !$permission->can_create) {
            return back()->with('error', 'You are not allowed to create this setting.');
        }

        SiteSetting::updateOrCreate(
            ['key' => $data['key']],
            [
                'group' => $data['group'] ?: $permission->group,
                'value' => $data['value'] ?? '',
            ]
        );

        return back()->with('success', 'Setting created successfully.');
    }

    public function destroy($key)
    {
        $permission = SellerSettingPermission::where('setting_key', $key)->first();
        if (!$permission || !$permission->can_delete) {
            return back()->with('error', 'You are not allowed to delete this setting.');
        }

        SiteSetting::where('key', $key)->delete();

        return back()->with('success', 'Setting deleted successfully.');
    }
}
