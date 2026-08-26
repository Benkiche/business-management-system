<div class="sidebar">
    <button type="button" class="sidebar-close" aria-label="Close navigation">
        <i class="fas fa-times"></i>
    </button>
    <div class="brand">
        <h5><i class="fas fa-chart-line"></i> BMS</h5>
        <small>Management System</small>
    </div>
    
    <nav class="nav flex-column">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        
        @if(auth()->check() && auth()->user()->hasPermission('users.view'))
            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
        @endif
        
        @if(auth()->check() && auth()->user()->hasPermission('roles.view'))
            <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                <i class="fas fa-shield-alt"></i>
                <span>Roles</span>
            </a>
        @endif
        
        <div style="border-top: 1px solid rgba(255,255,255,0.1); margin: 20px 0;"></div>
        <span style="padding: 0 20px; font-size: 12px; text-transform: uppercase; opacity: 0.6;">
            Business
        </span>
        
        @if(auth()->check() && auth()->user()->hasPermission('customers.view'))
            <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                <i class="fas fa-users-cog"></i>
                <span>Customers</span>
            </a>
        @endif
        
        <a href="{{ route('suppliers.index') }}" class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
            <i class="fas fa-truck"></i>
            <span>Suppliers</span>
        </a>
        
        @if(auth()->check() && auth()->user()->hasPermission('products.view'))
            <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                <i class="fas fa-box"></i>
                <span>Products</span>
            </a>
            
            <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" style="margin-left: 20px; font-size: 0.9rem; opacity: 0.8;">
                <i class="fas fa-tag"></i>
                <span>Categories</span>
            </a>
        @endif
        
        <div style="border-top: 1px solid rgba(255,255,255,0.1); margin: 20px 0;"></div>
        @if(auth()->check() && auth()->user()->hasPermission('sales.view'))
            <a href="{{ route('sales.index') }}" class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart"></i>
                <span>Sales</span>
            </a>
        @endif
        
        @if(auth()->check() && auth()->user()->hasPermission('inventory.view'))
    <a href="{{ route('inventory.index') }}" class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
        <i class="fas fa-warehouse"></i>
        <span>Inventory</span>
    </a>
@endif

        <div style="border-top: 1px solid rgba(255,255,255,0.1); margin: 20px 0;"></div>
<span style="padding: 0 20px; font-size: 12px; text-transform: uppercase; opacity: 0.6;">
    Financial
</span>

@if(auth()->check() && auth()->user()->hasPermission('expenses.view'))
    <a href="{{ route('expenses.index') }}" class="nav-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
        <i class="fas fa-receipt"></i>
        <span>Expenses</span>
    </a>
@endif

<a href="{{ route('financial.dashboard') }}" class="nav-link {{ request()->routeIs('financial.*') ? 'active' : '' }}">
    <i class="fas fa-chart-line"></i>
    <span>Financial Reports</span>
</a>

<div style="border-top: 1px solid rgba(255,255,255,0.1); margin: 20px 0;"></div>
<span style="padding: 0 20px; font-size: 12px; text-transform: uppercase; opacity: 0.6;">
    Analytics & Reports
</span>

<a href="{{ route('analytics.sales') }}" class="nav-link {{ request()->routeIs('analytics.sales') ? 'active' : '' }}">
    <i class="fas fa-chart-bar"></i>
    <span>Sales Analytics</span>
</a>

<a href="{{ route('analytics.inventory') }}" class="nav-link {{ request()->routeIs('analytics.inventory') ? 'active' : '' }}">
    <i class="fas fa-boxes"></i>
    <span>Inventory Analytics</span>
</a>

<a href="{{ route('analytics.customers') }}" class="nav-link {{ request()->routeIs('analytics.customers') ? 'active' : '' }}">
    <i class="fas fa-users-chart"></i>
    <span>Customer Analytics</span>
</a>

<a href="{{ route('reports.sales') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
    <i class="fas fa-file-alt"></i>
    <span>Reports</span>
</a>
<!-- Add this to your sidebar navigation menu -->

<!-- Main Navigation Items -->
<ul class="navbar-nav flex-column">
    <!-- ... existing menu items ... -->

    <!-- Conversations Menu -->
    <li class="nav-item">
        <a class="nav-link {{ Route::is('conversations.*', 'messages.*') ? 'active' : '' }}" 
           href="{{ route('conversations.index') }}">
            <i class="fas fa-comments me-2"></i>
            <span>Conversations</span>
            @php
                $unreadCount = auth()->user()->getUnreadConversationCount();
            @endphp
            @if($unreadCount > 0)
                <span class="badge bg-danger ms-auto">{{ $unreadCount }}</span>
            @endif
        </a>
    </li>
</ul>
    </nav>
</div>

