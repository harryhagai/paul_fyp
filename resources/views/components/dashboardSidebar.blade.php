@php
    $headerSettings = \App\Models\SiteSetting::where('group', 'header')->pluck('value', 'key');
    $sidebarBrandName = $headerSettings['header_school_name'] ?? config('app.name', 'Kids Shop');
    $sidebarLogo = $headerSettings['header_logo'] ?? null;
    $sidebarLogoUrl = $sidebarLogo
        ? (\Illuminate\Support\Str::startsWith($sidebarLogo, ['http://', 'https://'])
            ? $sidebarLogo
            : asset(ltrim($sidebarLogo, '/')))
        : asset('img/logo.png');
@endphp
<aside id="sidebar">
    <div class="p-3">
        <div class="seller-sidebar-brand mb-3">
            <img src="{{ $sidebarLogoUrl }}" alt="Kids Shop Logo" class="seller-sidebar-logo">
            <div class="seller-sidebar-brand-text">
                <div class="seller-sidebar-brand-title">{{ $sidebarBrandName }}</div>
                <div class="seller-sidebar-brand-subtitle">{{ Auth::check() ? ucfirst(Auth::user()->role) : 'Seller' }} Panel</div>
            </div>
        </div>

        <ul class="nav flex-column">
            @if(Auth::check() && Auth::user()->role === 'admin')
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.customers') }}" class="nav-link {{ request()->is('admin/customers*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i> Users
                    </a>
                </li>
                @php
                    $adminSettingsOpen = request()->is('admin/settings/header*') || request()->is('admin/settings/footer*') || request()->is('admin/settings/mail*') || request()->is('admin/settings/seller-permissions*') || request()->is('admin/settings/orders*');
                @endphp
                <li class="nav-item sidebar-menu-group">
                    <button class="nav-link sidebar-menu-toggle {{ $adminSettingsOpen ? 'active' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#adminSettingsMenu" aria-expanded="{{ $adminSettingsOpen ? 'true' : 'false' }}" aria-controls="adminSettingsMenu">
                        <i class="bi bi-gear-fill"></i>
                        <span>Settings</span>
                        <i class="bi bi-chevron-down sidebar-menu-chevron"></i>
                    </button>
                    <div id="adminSettingsMenu" class="collapse sidebar-submenu {{ $adminSettingsOpen ? 'show' : '' }}">
                        <a href="{{ route('admin.settings.header') }}" class="nav-link sidebar-submenu-link {{ request()->is('admin/settings/header*') ? 'active' : '' }}">
                            <i class="bi bi-layout-text-window-reverse"></i> Header
                        </a>
                        <a href="{{ route('admin.settings.footer') }}" class="nav-link sidebar-submenu-link {{ request()->is('admin/settings/footer*') ? 'active' : '' }}">
                            <i class="bi bi-layout-text-window"></i> Footer
                        </a>
                        <a href="{{ route('admin.settings.mail') }}" class="nav-link sidebar-submenu-link {{ request()->is('admin/settings/mail*') ? 'active' : '' }}">
                            <i class="bi bi-envelope-fill"></i> Mail
                        </a>
                        <a href="{{ route('admin.settings.seller-permissions') }}" class="nav-link sidebar-submenu-link {{ request()->is('admin/settings/seller-permissions*') ? 'active' : '' }}">
                            <i class="bi bi-shield-check"></i> Permissions
                        </a>
                        <a href="{{ route('admin.settings.orders') }}" class="nav-link sidebar-submenu-link {{ request()->is('admin/settings/orders*') ? 'active' : '' }}">
                            <i class="bi bi-clock-history"></i> Orders Timer
                        </a>
                    </div>
                </li>
            @elseif(Auth::check() && Auth::user()->role === 'seller')
                <li class="nav-item"><a href="{{ route('seller.dashboard') }}" class="nav-link {{ request()->is('seller/dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li class="sidebar-section-label">Store</li>
                <li class="nav-item"><a href="{{ route('seller.categories') }}" class="nav-link {{ request()->is('seller/categories') ? 'active' : '' }}"><i class="bi bi-tags"></i> Categories</a></li>
                <li class="nav-item"><a href="{{ route('seller.products') }}" class="nav-link {{ request()->is('seller/products*') ? 'active' : '' }}"><i class="bi bi-basket-fill"></i> Products</a></li>
                <li class="sidebar-section-label">Sales</li>
                <li class="nav-item"><a href="{{ route('seller.orders') }}" class="nav-link {{ request()->is('seller/orders*') ? 'active' : '' }}"><i class="bi bi-receipt"></i> Orders</a></li>
                <li class="nav-item"><a href="{{ route('seller.customers') }}" class="nav-link {{ request()->is('seller/customers') ? 'active' : '' }}"><i class="bi bi-people-fill"></i> Customers</a></li>
                <li class="nav-item"><a href="{{ route('seller.notifications.index') }}" class="nav-link {{ request()->is('seller/notifications*') ? 'active' : '' }}"><i class="bi bi-bell-fill"></i> Notifications</a></li>
                @php
                    $settingsOpen = request()->is('seller/settings*');
                @endphp
                <li class="nav-item sidebar-menu-group">
                    <button class="nav-link sidebar-menu-toggle {{ $settingsOpen ? 'active' : '' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sellerSettingsMenu" aria-expanded="{{ $settingsOpen ? 'true' : 'false' }}" aria-controls="sellerSettingsMenu">
                        <i class="bi bi-gear-fill"></i>
                        <span>Settings</span>
                        <i class="bi bi-chevron-down sidebar-menu-chevron"></i>
                    </button>
                    <div id="sellerSettingsMenu" class="collapse sidebar-submenu {{ $settingsOpen ? 'show' : '' }}">
                        <a href="{{ route('seller.settings.header') }}" class="nav-link sidebar-submenu-link {{ request()->is('seller/settings/header*') ? 'active' : '' }}">
                            <i class="bi bi-layout-text-window-reverse"></i> Header
                        </a>
                        <a href="{{ route('seller.settings.footer') }}" class="nav-link sidebar-submenu-link {{ request()->is('seller/settings/footer*') ? 'active' : '' }}">
                            <i class="bi bi-layout-text-window"></i> Footer
                        </a>
                        <a href="{{ route('seller.settings.orders') }}" class="nav-link sidebar-submenu-link {{ request()->is('seller/settings/orders*') ? 'active' : '' }}">
                            <i class="bi bi-clock-history"></i> Orders Timer
                        </a>
                    </div>
                </li>
            @elseif(Auth::check() && Auth::user()->role === 'customer')
                <li class="nav-item"><a href="{{ route('customer.dashboard') }}" class="nav-link {{ request()->is('customer/dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li class="nav-item"><a href="{{ route('customer.orders') }}" class="nav-link {{ request()->is('customer/orders*') || request()->is('customer/order*') ? 'active' : '' }}"><i class="bi bi-receipt"></i> My Orders</a></li>
                <li class="nav-item"><a href="{{ route('customer.profile') }}" class="nav-link {{ request()->is('customer/profile*') ? 'active' : '' }}"><i class="bi bi-person"></i> My Profile</a></li>
            @endif
        </ul>
    </div>

    <div class="seller-sidebar-bottom">
        @auth
            <div class="px-3 pb-2">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="nav-link w-100 bg-transparent border-0 text-start sidebar-logout-link">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        @endauth

        <div class="seller-sidebar-footer">
            <span class="seller-sidebar-footer-full">&copy; {{ date('Y') }} {{ $sidebarBrandName }}</span>
            <span class="seller-sidebar-footer-compact">&copy; {{ date('Y') }}</span>
        </div>
    </div>
</aside>
