@php
    $settingsRoutePrefix = auth()->check() && auth()->user()->role === 'admin' ? 'admin' : 'seller';
@endphp
<div class="card panel-card settings-page-tabs-wrap">
    <div class="panel-card-body p-2">
        <ul class="nav settings-page-tabs">
            <li class="nav-item">
                <a href="{{ route($settingsRoutePrefix . '.settings.header') }}" class="settings-nav-link {{ ($active ?? '') === 'header' ? 'active' : '' }}">
                    <i class="bi bi-layout-text-window-reverse"></i>
                    <span>Header</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route($settingsRoutePrefix . '.settings.footer') }}" class="settings-nav-link {{ ($active ?? '') === 'footer' ? 'active' : '' }}">
                    <i class="bi bi-layout-text-window"></i>
                    <span>Footer</span>
                </a>
            </li>
            <li class="nav-item">
                @if($settingsRoutePrefix === 'admin')
                <a href="{{ route('admin.settings.mail') }}" class="settings-nav-link {{ ($active ?? '') === 'mail' ? 'active' : '' }}">
                    <i class="bi bi-envelope-fill"></i>
                    <span>Mail</span>
                </a>
                @endif
                <a href="{{ route($settingsRoutePrefix . '.settings.orders') }}" class="settings-nav-link {{ ($active ?? '') === 'orders' ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i>
                    <span>Orders</span>
                </a>
            </li>
        </ul>
    </div>
</div>
