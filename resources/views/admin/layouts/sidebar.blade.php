<!-- BEGIN #sidebar -->
<div id="sidebar" class="app-sidebar">
    <!-- BEGIN scrollbar -->
    <div class="app-sidebar-content" data-scrollbar="true" data-height="100%">
        <!-- BEGIN menu -->
        <div class="menu mt-3">
            <div class="menu-item">
                <a href="{{ route('admin.dashboard') }}" class="menu-link">
                    <span class="menu-icon"><i class="bi bi-cpu"></i></span>
                    <span class="menu-text">Dashboard</span>
                </a>
            </div>
            @can('users.index')
                <div class="menu-item">
                    <a href="{{ route('users.index') }}" class="menu-link">
                        <span class="menu-icon"><i class="bi bi-people-fill"></i></span>
                        <span class="menu-text">Manage Users</span>
                    </a>
                </div>
            @endcan
            @can('roles.index')
                <div class="menu-item">
                    <a href="{{ route('roles.index') }}" class="menu-link">
                        <span class="menu-icon"><i class="bi bi-person-fill-gear"></i></span>
                        <span class="menu-text">Roles & Permissions</span>
                    </a>
                </div>
            @endcan
            <div class="menu-item has-sub">
                <a href="#" class="menu-link">
                    <span class="menu-icon">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <span class="menu-text">Email</span>
                    <span class="menu-caret"><b class="caret"></b></span>
                </a>
                <div class="menu-submenu">
                    <div class="menu-item">
                        <a href="#" class="menu-link">
                            <span class="menu-text">Inbox</span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a href="#" class="menu-link">
                            <span class="menu-text">Compose</span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a href="#" class="menu-link">
                            <span class="menu-text">Detail</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- END menu -->
        <div class="p-3 px-4 mt-auto">
            <a href="documentation/index.html" target="_blank" class="btn d-block btn-outline-theme">
                <i class="fa fa-code-branch me-2 ms-n2 opacity-5"></i> Documentation
            </a>
        </div>
    </div>
    <!-- END scrollbar -->
</div>
<!-- END #sidebar -->

<!-- BEGIN mobile-sidebar-backdrop -->
<button class="app-sidebar-mobile-backdrop" data-toggle-target=".app"
    data-toggle-class="app-sidebar-mobile-toggled"></button>
<!-- END mobile-sidebar-backdrop -->
