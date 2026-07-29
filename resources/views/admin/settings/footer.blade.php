@extends('layouts.dashboard')

@section('styles')
<link href="{{ asset('css/admin-settings.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container-fluid mt-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-layout-text-window me-3"></i>Footer Settings</h1>
    </div>

    <div class="row g-4">
        <div class="col-12">
            @include('admin.settings._nav', ['active' => 'footer'])
        </div>

        <div class="col-12">
            <div class="card panel-card">
                <div class="panel-card-head"><h5 class="panel-card-title">Manage Footer Content</h5></div>
                <div class="panel-card-body">
                    <form id="footer-settings-form" action="{{ route((auth()->user()->role === 'admin' ? 'admin' : 'seller') . '.settings.footer.update') }}" method="POST" novalidate>
                        @csrf
                        @if ($errors->any())
                            <div class="alert alert-danger" role="alert">
                                {{ $errors->first() }}
                            </div>
                        @endif
                        <p class="section-note">Brand, contact details and social links shown in footer.</p>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Footer Brand Name</label><input type="text" class="form-control" name="footer_brand_name" value="{{ $settings['footer_brand_name'] ?? 'myKidsShop365' }}"></div>
                            <div class="col-md-6"><label class="form-label">Footer Brand Subtitle</label><input type="text" class="form-control" name="footer_brand_subtitle" value="{{ $settings['footer_brand_subtitle'] ?? 'Everything Cute for Little Ones' }}"></div>
                            <div class="col-12"><label class="form-label">Footer Description</label><textarea class="form-control" name="footer_description" rows="3">{{ $settings['footer_description'] ?? 'Your #1 trusted store for baby clothes, toys, accessories, maternity items, and every adorable thing your child deserves.' }}</textarea></div>
                            <div class="col-12"><label class="form-label">Physical Address</label><input type="text" class="form-control" name="footer_contact_address" value="{{ $settings['footer_contact_address'] ?? 'Kids Plaza, Dar es Salaam, Tanzania' }}"></div>
                            <div class="col-md-6"><label class="form-label">Phone Number</label><input type="text" class="form-control" name="footer_contact_phone" value="{{ $settings['footer_contact_phone'] ?? '+255 712 345 678' }}"></div>
                            <div class="col-md-6"><label class="form-label">Email Address</label><input type="email" class="form-control" name="footer_contact_email" value="{{ $settings['footer_contact_email'] ?? 'support@mykidsshop365.com' }}"></div>
                            <div class="col-12"><label class="form-label">Working Hours</label><input type="text" class="form-control" name="footer_contact_hours" value="{{ $settings['footer_contact_hours'] ?? 'Mon - Sat: 9:00 AM - 7:00 PM' }}"></div>
                            <div class="col-md-6"><label class="form-label">Facebook URL</label><input type="text" inputmode="url" class="form-control" name="footer_social_facebook" value="{{ $settings['footer_social_facebook'] ?? '#' }}"></div>
                            <div class="col-md-6"><label class="form-label">Instagram URL</label><input type="text" inputmode="url" class="form-control" name="footer_social_instagram" value="{{ $settings['footer_social_instagram'] ?? '#' }}"></div>
                            <div class="col-md-6"><label class="form-label">TikTok URL</label><input type="text" inputmode="url" class="form-control" name="footer_social_tiktok" value="{{ $settings['footer_social_tiktok'] ?? '#' }}"></div>
                            <div class="col-md-6"><label class="form-label">YouTube URL</label><input type="text" inputmode="url" class="form-control" name="footer_social_youtube" value="{{ $settings['footer_social_youtube'] ?? '#' }}"></div>
                            <div class="col-12"><label class="form-label">Copyright Text</label><input type="text" class="form-control" name="footer_copyright" value="{{ $settings['footer_copyright'] ?? '� 2025 myKidsShop365. All rights reserved.' }}"></div>
                        </div>

                        <div class="text-end mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-brand"><i class="bi bi-save me-1"></i> Save Footer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('footer-settings-form')?.addEventListener('submit', function () {
        this.querySelectorAll('input[name]:not([type="hidden"]), textarea[name], select[name]').forEach((field) => {
            if (field.value === field.defaultValue) {
                field.disabled = true;
            }
        });
    });
</script>
@endsection
