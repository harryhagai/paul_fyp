<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FooterSettingsUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_one_footer_field_without_resetting_the_others(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        SiteSetting::create([
            'key' => 'footer_brand_name',
            'group' => 'footer',
            'value' => 'Old Brand',
        ]);

        SiteSetting::create([
            'key' => 'footer_contact_phone',
            'group' => 'footer',
            'value' => '+255 700 000 000',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.settings.footer.update'), [
            'footer_brand_name' => 'New Brand',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('site_settings', [
            'key' => 'footer_brand_name',
            'group' => 'footer',
            'value' => 'New Brand',
        ]);

        $this->assertDatabaseHas('site_settings', [
            'key' => 'footer_contact_phone',
            'group' => 'footer',
            'value' => '+255 700 000 000',
        ]);
    }
}
