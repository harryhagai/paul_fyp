<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class SiteSettingController extends Controller
{
    public function headerSettings()
    {
        $settings = \App\Models\SiteSetting::where('group', 'header')->pluck('value', 'key')->toArray();
        return view('admin.settings.header', compact('settings'));
    }

    public function updateHeaderSettings(Request $request)
    {
        $data = $request->except(['_token']);
        
        foreach ($data as $key => $value) {
            if ($request->hasFile($key)) {
                $file = $request->file($key);
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('img/settings'), $filename);
                $value = 'img/settings/' . $filename;
            }

            \App\Models\SiteSetting::updateOrCreate(
                ['key' => $key, 'group' => 'header'],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', 'Header settings updated successfully!');
    }

    public function footerSettings()
    {
        $settings = \App\Models\SiteSetting::where('group', 'footer')->pluck('value', 'key')->toArray();
        return view('admin.settings.footer', compact('settings'));
    }

    public function updateFooterSettings(Request $request)
    {
        $data = $request->validate([
            'footer_brand_name' => ['sometimes', 'nullable', 'string', 'max:191'],
            'footer_brand_subtitle' => ['sometimes', 'nullable', 'string', 'max:191'],
            'footer_description' => ['sometimes', 'nullable', 'string'],
            'footer_contact_address' => ['sometimes', 'nullable', 'string', 'max:500'],
            'footer_contact_phone' => ['sometimes', 'nullable', 'string', 'max:100'],
            'footer_contact_email' => ['sometimes', 'nullable', 'email', 'max:191'],
            'footer_contact_hours' => ['sometimes', 'nullable', 'string', 'max:191'],
            'footer_social_facebook' => ['sometimes', 'nullable', 'string', 'max:500'],
            'footer_social_instagram' => ['sometimes', 'nullable', 'string', 'max:500'],
            'footer_social_tiktok' => ['sometimes', 'nullable', 'string', 'max:500'],
            'footer_social_youtube' => ['sometimes', 'nullable', 'string', 'max:500'],
            'footer_copyright' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);

        $currentSettings = SiteSetting::where('group', 'footer')
            ->whereIn('key', array_keys($data))
            ->get()
            ->keyBy('key');

        foreach ($data as $key => $value) {
            $value = is_string($value) ? trim($value) : $value;
            $setting = $currentSettings->get($key);

            if ($setting && $setting->value === $value) {
                continue;
            }

            SiteSetting::updateOrCreate(
                ['key' => $key],
                ['group' => 'footer', 'value' => $value]
            );
        }

        return redirect()->back()->with('success', 'Footer settings updated successfully!');
    }

    public function mailSettings()
    {
        $settings = \App\Models\SiteSetting::where('group', 'mail')->pluck('value', 'key')->toArray();
        return view('admin.settings.mail', compact('settings'));
    }

    public function orderSettings()
    {
        $settings = \App\Models\SiteSetting::where('group', 'orders')->pluck('value', 'key')->toArray();
        return view('admin.settings.orders', compact('settings'));
    }

    public function updateOrderSettings(Request $request)
    {
        $validated = $request->validate([
            'order_auto_cancel_hours' => ['required', 'integer', 'min:0', 'max:720'],
            'order_auto_cancel_minutes' => ['required', 'integer', 'min:0', 'max:59'],
            'order_auto_cancel_seconds' => ['required', 'integer', 'min:0', 'max:59'],
        ]);

        foreach ($validated as $key => $value) {
            \App\Models\SiteSetting::updateOrCreate(
                ['key' => $key],
                ['group' => 'orders', 'value' => (string) $value]
            );
        }

        return redirect()->back()->with('success', 'Order auto-cancel timer updated successfully!');
    }

    public function updateMailSettings(Request $request)
    {
        $validated = $request->validate([
            'mail_mailer' => ['required', 'in:smtp'],
            'mail_host' => ['required', 'string', 'max:191'],
            'mail_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:191'],
            'mail_password' => ['nullable', 'string', 'max:1000'],
            'mail_encryption' => ['nullable', 'in:tls,ssl'],
            'mail_from_address' => ['required', 'email', 'max:191'],
            'mail_from_name' => ['required', 'string', 'max:191'],
        ]);

        foreach ($validated as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
            }

            \App\Models\SiteSetting::updateOrCreate(
                ['key' => $key, 'group' => 'mail'],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', 'Mail settings updated successfully!');
    }
}
