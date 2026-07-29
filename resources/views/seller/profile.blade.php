@extends('layouts.dashboard')

@section('title', 'Profile - KidsStore Seller')

@section('content')
@php
    $isAdmin = auth()->check() && auth()->user()->role === 'admin';
    $profileUpdateRoute = $isAdmin ? route('admin.profile.update') : route('seller.profile.update');
    $dashboardRoute = $isAdmin ? route('admin.dashboard') : route('seller.dashboard');
@endphp
<div class="container-fluid admin-shell admin-shell-fit">
    <div class="page-head">
        <h1 class="page-title"><i class="bi bi-person-circle"></i> My Profile</h1>
    </div>

    <div class="panel-card">
        <div class="panel-card-body">
            <div class="row g-4 align-items-center">
                <div class="col-md-3 text-center">
                    @if($seller->profile_photo)
                        <img src="{{ asset('storage/profile_photos/' . $seller->profile_photo) }}" alt="Profile Photo" class="rounded-circle" style="width: 140px; height: 140px; object-fit: cover; border: 3px solid #e2e8f0;">
                    @else
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 140px; height: 140px; border: 3px solid #e2e8f0;">
                            <i class="bi bi-person-fill text-muted" style="font-size: 3.5rem;"></i>
                        </div>
                    @endif
                </div>
                <div class="col-md-9">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" value="{{ $seller->name }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="text" class="form-control" value="{{ $seller->email }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="{{ ucfirst($seller->role) }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Member Since</label>
                            <input type="text" class="form-control" value="{{ $seller->created_at->format('M d, Y') }}" readonly>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateProfileModal">
                            <i class="bi bi-pencil-square"></i> Update Profile
                        </button>
                        <a href="{{ $dashboardRoute }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="updateProfileModal" tabindex="-1" aria-labelledby="updateProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ $profileUpdateRoute }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title" id="updateProfileModalLabel">Update Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $seller->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $seller->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label for="profile_photo" class="form-label">Profile Photo</label>
                            <input type="file" class="form-control @error('profile_photo') is-invalid @enderror" id="profile_photo" name="profile_photo" accept="image/*">
                            @error('profile_photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12"><hr></div>
                        <div class="col-12"><small class="text-muted">Leave password fields empty if you do not want to change password.</small></div>

                        <div class="col-md-4">
                            <label for="current_password" class="form-label">Current Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password"><i class="bi bi-eye"></i></button>
                                @error('current_password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="password" class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password"><i class="bi bi-eye"></i></button>
                            </div>
                            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.toggle-password').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const target = document.getElementById(this.dataset.target);
        const icon = this.querySelector('i');
        if (!target) return;
        if (target.type === 'password') {
            target.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            target.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });
});

@if ($errors->any())
document.addEventListener('DOMContentLoaded', function () {
    const modal = new bootstrap.Modal(document.getElementById('updateProfileModal'));
    modal.show();
});
@endif
</script>
@endsection
