@extends('layouts.dashboard')

@section('styles')
<link href="{{ asset('css/admin-settings.css') }}" rel="stylesheet">
<link href="{{ asset('css/admin-seller-permissions.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="container-fluid mt-2">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-shield-lock me-3"></i>Seller Edit Permissions</h1>
    </div>

    @php
        $allowedGroups = ['header', 'footer'];
        $groups = collect($components)->keys()->values()->filter(fn($g) => in_array($g, $allowedGroups))->values();
    @endphp

    <div class="card panel-card">
        <div class="panel-card-head p-0">
            <ul class="nav nav-tabs settings-tabs" id="permissionGroupsTab" role="tablist">
                @foreach($groups as $group)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="tab-{{ $group }}" data-bs-toggle="tab" data-bs-target="#pane-{{ $group }}" type="button" role="tab">
                            {{ ucfirst($group) }}
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="panel-card-body">
            <form method="POST" action="{{ route('admin.settings.seller-permissions.bulk-update') }}">
                @csrf
                @method('PUT')

                <div class="tab-content" id="permissionGroupsContent">
                    @foreach($groups as $group)
                        @php $groupComponents = $components[$group] ?? []; @endphp
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="pane-{{ $group }}" role="tabpanel">
                            <p class="section-note">Allow or deny seller editing for each full {{ $group }} component.</p>
                            <div class="row g-3">
                                @foreach($groupComponents as $component)
                                    <div class="col-lg-6">
                                        <div class="permission-component-card">
                                            <div class="d-flex justify-content-between align-items-start gap-3">
                                                <div>
                                                    <div class="permission-title">{{ $component['label'] }}</div>
                                                    <div class="permission-desc">{{ $component['description'] }}</div>
                                                </div>
                                                <div class="form-check form-switch m-0">
                                                    <input
                                                        class="form-check-input permission-toggle"
                                                        type="checkbox"
                                                        name="components[{{ $group }}][{{ $component['id'] }}]"
                                                        id="component_{{ $group }}_{{ $component['id'] }}"
                                                        {{ !empty($component['enabled']) ? 'checked' : '' }}
                                                    >
                                                    <label class="form-check-label" for="component_{{ $group }}_{{ $component['id'] }}">Can Edit</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="text-end mt-4 pt-3 border-top">
                    <button type="submit" class="btn btn-brand"><i class="bi bi-save me-1"></i> Save Permissions</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
