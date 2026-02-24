<nav class="main-header navbar navbar-expand navbar-white navbar-light">

    {{-- Left navbar links --}}
  <ul class="navbar-nav">
    <!-- Hamburger menu -->
    <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button">
            <i class="fas fa-bars"></i>
        </a>
    </li>

    <!-- App Name -->
    <li class="nav-item d-none d-sm-inline-block">
        <a href="{{ url('/') }}" class="nav-link">
            {{ config('app.name') }}
        </a>
    </li>
</ul>


    {{-- Right navbar links --}}
    <ul class="navbar-nav ml-auto align-items-center">
        {{-- Notification Bell --}}
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <i class="far fa-bell"></i>
                <span class="badge badge-danger navbar-badge">
                    {{ \App\Models\LeaveRequest::whereIn('status', ['pending','on_progress'])->count() }}
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <span class="dropdown-item">New pending leave requests</span>
                <div class="dropdown-divider"></div>
                <a href="{{ route('leaves.pending') }}" class="dropdown-item">View Pending Leaves</a>
            </div>
        </li>
        {{-- Search Bar --}}
        <li class="nav-item mx-2">
            <form class="form-inline" action="{{ route('leaves.index') }}" method="GET">
                <div class="input-group input-group-sm">
                    <input class="form-control form-control-navbar" type="search" name="search" placeholder="Search leaves/users" aria-label="Search">
                    <div class="input-group-append">
                        <button class="btn btn-navbar" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </li>
        {{-- User Dropdown with Image --}}
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#">
                <img src="{{ auth()->user()->profile_image ?? '/default-profile.png' }}" class="rounded-circle" width="32" height="32" alt="Profile">
                <span class="d-none d-md-inline ml-1">
                    {{ auth()->user()->full_name ?? 'User' }}
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a href="{{ route('profile') }}" class="dropdown-item">
                    <i class="fas fa-user-circle mr-2"></i> Profile Settings
                </a>
                <div class="dropdown-divider"></div>
                <a href="{{ route('logout') }}" class="dropdown-item text-danger"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </li>
    </ul>
</nav>
