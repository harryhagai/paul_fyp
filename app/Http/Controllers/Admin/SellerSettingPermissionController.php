<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SellerSettingPermission;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SellerSettingPermissionController extends Controller
{
    public function index()
    {
        $this->syncDefaultPermissions();

        $permissions = SellerSettingPermission::orderBy('group')->orderBy('setting_key')->get()->keyBy('setting_key');
        $components = $this->permissionComponents();

        foreach ($components as $group => &$groupComponents) {
            foreach ($groupComponents as &$component) {
                $component['enabled'] = collect($component['keys'])->every(function ($key) use ($permissions) {
                    return $permissions->has($key) && (bool) $permissions[$key]->can_update;
                });
            }
        }

        return view('admin.settings.seller-permissions', compact('components'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'setting_key' => 'required|string|max:191|unique:seller_setting_permissions,setting_key',
            'group' => 'required|string|max:100',
            'label' => 'nullable|string|max:191',
            'description' => 'nullable|string',
            'can_view' => 'nullable|boolean',
            'can_create' => 'nullable|boolean',
            'can_update' => 'nullable|boolean',
            'can_delete' => 'nullable|boolean',
        ]);

        foreach (['can_view', 'can_create', 'can_update', 'can_delete'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }

        SellerSettingPermission::create($data);

        return back()->with('success', 'Permission created successfully.');
    }

    public function bulkUpdate(Request $request)
    {
        $submitted = $request->input('components', []);
        $components = $this->permissionComponents();

        foreach ($components as $group => $groupComponents) {
            foreach ($groupComponents as $component) {
                $enabled = isset($submitted[$group][$component['id']]);
                SellerSettingPermission::whereIn('setting_key', $component['keys'])->update(['can_update' => $enabled]);
            }
        }

        return back()->with('success', 'Permissions updated successfully.');
    }

    public function update(Request $request, $id)
    {
        $permission = SellerSettingPermission::wherePublicIdOrId($id)->firstOrFail();

        $data = $request->validate([
            'setting_key' => 'required|string|max:191|unique:seller_setting_permissions,setting_key,' . $permission->id,
            'group' => 'required|string|max:100',
            'label' => 'nullable|string|max:191',
            'description' => 'nullable|string',
            'can_view' => 'nullable|boolean',
            'can_create' => 'nullable|boolean',
            'can_update' => 'nullable|boolean',
            'can_delete' => 'nullable|boolean',
        ]);

        foreach (['can_view', 'can_create', 'can_update', 'can_delete'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }

        $permission->update($data);

        return back()->with('success', 'Permission updated successfully.');
    }

    public function destroy($id)
    {
        SellerSettingPermission::wherePublicIdOrId($id)->firstOrFail()->delete();
        return back()->with('success', 'Permission deleted successfully.');
    }

    private function syncDefaultPermissions(): void
    {
        $defaultMap = $this->defaultSettingsMap();

        foreach ($defaultMap as $key => $meta) {
            SellerSettingPermission::firstOrCreate(
                ['setting_key' => $key],
                [
                    'group' => $meta['group'],
                    'label' => $meta['label'],
                    'description' => $meta['description'],
                    'can_view' => true,
                    'can_create' => false,
                    'can_update' => false,
                    'can_delete' => false,
                ]
            );
        }

        // Also include any existing site settings keys not in the hardcoded map.
        $existing = SiteSetting::select('key', 'group')->get();
        foreach ($existing as $setting) {
            SellerSettingPermission::firstOrCreate(
                ['setting_key' => $setting->key],
                [
                    'group' => $setting->group ?: 'general',
                    'label' => Str::of($setting->key)->replace('_', ' ')->title(),
                    'description' => 'Auto-generated from existing site setting key.',
                    'can_view' => true,
                    'can_create' => false,
                    'can_update' => false,
                    'can_delete' => false,
                ]
            );
        }
    }

    private function defaultSettingsMap(): array
    {
        $map = [
            'header_school_name' => ['group' => 'header', 'label' => 'Header Brand Name', 'description' => 'Brand name shown in header.'],
            'header_school_subtitle' => ['group' => 'header', 'label' => 'Header Brand Subtitle', 'description' => 'Subtitle shown under brand name in header.'],
            'header_logo' => ['group' => 'header', 'label' => 'Header Logo', 'description' => 'Main logo image.'],

            'footer_brand_name' => ['group' => 'footer', 'label' => 'Footer Brand Name', 'description' => 'Brand name shown in footer.'],
            'footer_brand_subtitle' => ['group' => 'footer', 'label' => 'Footer Brand Subtitle', 'description' => 'Footer brand subtitle.'],
            'footer_description' => ['group' => 'footer', 'label' => 'Footer Description', 'description' => 'Footer description paragraph.'],
            'footer_contact_address' => ['group' => 'footer', 'label' => 'Footer Address', 'description' => 'Business physical address.'],
            'footer_contact_phone' => ['group' => 'footer', 'label' => 'Footer Phone', 'description' => 'Footer phone number.'],
            'footer_contact_email' => ['group' => 'footer', 'label' => 'Footer Email', 'description' => 'Footer contact email.'],
            'footer_contact_hours' => ['group' => 'footer', 'label' => 'Footer Working Hours', 'description' => 'Working hours text.'],
            'footer_social_facebook' => ['group' => 'footer', 'label' => 'Facebook URL', 'description' => 'Footer Facebook link.'],
            'footer_social_instagram' => ['group' => 'footer', 'label' => 'Instagram URL', 'description' => 'Footer Instagram link.'],
            'footer_social_tiktok' => ['group' => 'footer', 'label' => 'TikTok URL', 'description' => 'Footer TikTok link.'],
            'footer_social_youtube' => ['group' => 'footer', 'label' => 'YouTube URL', 'description' => 'Footer YouTube link.'],
            'footer_copyright' => ['group' => 'footer', 'label' => 'Footer Copyright', 'description' => 'Copyright line.'],
        ];

        $homeKeys = [
            'hero_badge_1', 'hero_badge_2', 'hero_title', 'hero_subtitle', 'hero_image',
            'hero_cta_primary_text', 'hero_cta_primary_url', 'hero_cta_secondary_text', 'hero_cta_secondary_url',
            'trust_badge_1', 'trust_badge_2', 'trust_badge_3',
            'hero_float_title', 'hero_float_subtitle', 'hero_rating_value', 'hero_rating_label',
            'values_eyebrow', 'values_title',
            'categories_eyebrow', 'categories_title', 'categories_empty_text', 'categories_button_text',
            'features_eyebrow', 'features_title',
            'contact_title',
            'testimonial_enabled', 'testimonial_author', 'testimonial_text', 'testimonial_rating',
            'theme_primary_color', 'theme_secondary_color', 'theme_bg_color',
        ];

        foreach ($homeKeys as $key) {
            $map[$key] = [
                'group' => 'home',
                'label' => Str::of($key)->replace('_', ' ')->title(),
                'description' => 'Home page setting.',
            ];
        }

        for ($i = 1; $i <= 4; $i++) {
            $map["service_{$i}_icon"] = ['group' => 'home', 'label' => "Service {$i} Icon", 'description' => 'Home values card icon class.'];
            $map["service_{$i}_title"] = ['group' => 'home', 'label' => "Service {$i} Title", 'description' => 'Home values card title.'];
            $map["service_{$i}_desc"] = ['group' => 'home', 'label' => "Service {$i} Description", 'description' => 'Home values card description.'];
        }

        for ($i = 1; $i <= 4; $i++) {
            $map["contact_{$i}_icon"] = ['group' => 'home', 'label' => "Contact Tile {$i} Icon", 'description' => 'Home contact tile icon class.'];
            $map["contact_{$i}_title"] = ['group' => 'home', 'label' => "Contact Tile {$i} Title", 'description' => 'Home contact tile title.'];
            $map["contact_{$i}_desc"] = ['group' => 'home', 'label' => "Contact Tile {$i} Description", 'description' => 'Home contact tile description.'];
            $map["contact_{$i}_url"] = ['group' => 'home', 'label' => "Contact Tile {$i} URL", 'description' => 'Home contact tile link or action URL.'];
        }

        return $map;
    }

    private function permissionComponents(): array
    {
        return [
            'home' => [
                [
                    'id' => 'hero',
                    'label' => 'Hero',
                    'description' => 'Hero section content, CTA, trust badges and hero image.',
                    'keys' => [
                        'hero_badge_1', 'hero_badge_2', 'hero_title', 'hero_subtitle', 'hero_image',
                        'hero_cta_primary_text', 'hero_cta_primary_url', 'hero_cta_secondary_text', 'hero_cta_secondary_url',
                        'trust_badge_1', 'trust_badge_2', 'trust_badge_3',
                        'hero_float_title', 'hero_float_subtitle', 'hero_rating_value', 'hero_rating_label',
                    ],
                ],
                [
                    'id' => 'values',
                    'label' => 'Values',
                    'description' => 'Values heading and cards (icon, title, description).',
                    'keys' => [
                        'values_eyebrow', 'values_title',
                        'service_1_icon', 'service_1_title', 'service_1_desc',
                        'service_2_icon', 'service_2_title', 'service_2_desc',
                        'service_3_icon', 'service_3_title', 'service_3_desc',
                        'service_4_icon', 'service_4_title', 'service_4_desc',
                    ],
                ],
                [
                    'id' => 'categories',
                    'label' => 'Categories',
                    'description' => 'Categories section labels and empty-state text.',
                    'keys' => ['categories_eyebrow', 'categories_title', 'categories_empty_text', 'categories_button_text'],
                ],
                [
                    'id' => 'features',
                    'label' => 'Contact',
                    'description' => 'Contact section heading and contact tiles.',
                    'keys' => [
                        'contact_title',
                        'contact_1_icon', 'contact_1_title', 'contact_1_desc', 'contact_1_url',
                        'contact_2_icon', 'contact_2_title', 'contact_2_desc', 'contact_2_url',
                        'contact_3_icon', 'contact_3_title', 'contact_3_desc', 'contact_3_url',
                        'contact_4_icon', 'contact_4_title', 'contact_4_desc', 'contact_4_url',
                    ],
                ],
                [
                    'id' => 'colors',
                    'label' => 'Colors',
                    'description' => 'Theme primary, secondary and background colors.',
                    'keys' => ['theme_primary_color', 'theme_secondary_color', 'theme_bg_color'],
                ],
            ],
            'header' => [
                [
                    'id' => 'header_branding',
                    'label' => 'Header Branding',
                    'description' => 'Header logo, brand name and subtitle.',
                    'keys' => ['header_school_name', 'header_school_subtitle', 'header_logo'],
                ],
            ],
            'footer' => [
                [
                    'id' => 'footer_content',
                    'label' => 'Footer Content',
                    'description' => 'Footer brand, contacts, social links and copyright.',
                    'keys' => [
                        'footer_brand_name', 'footer_brand_subtitle', 'footer_description',
                        'footer_contact_address', 'footer_contact_phone', 'footer_contact_email', 'footer_contact_hours',
                        'footer_social_facebook', 'footer_social_instagram', 'footer_social_tiktok', 'footer_social_youtube',
                        'footer_copyright',
                    ],
                ],
            ],
        ];
    }
}
