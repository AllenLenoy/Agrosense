<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — AgroSense</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --primary: #219653;
            --primary-dark: #1e874b;
            --primary-light: #e8f5e9;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            overflow: hidden;
            display: flex;
            height: 100vh;
            margin: 0;
        }

        /* Sidebar styling */
        .sidebar {
            width: 260px;
            background: var(--bg-card);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            z-index: 50;
            transition: transform 0.3s ease;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 40;
        }

        @media (max-width: 1024px) {
            .sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .sidebar-overlay.show {
                display: block;
            }
        }

        .brand-header {
            height: 72px;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            border-bottom: 1px solid var(--border-color);
            text-decoration: none;
            color: var(--text-main);
        }

        .brand-icon {
            width: 32px;
            height: 32px;
            background: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.9rem;
            margin-right: 0.75rem;
            box-shadow: 0 4px 10px rgba(33, 150, 83, 0.2);
        }

        .brand-text {
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .nav-section {
            padding: 1.5rem 1rem 0;
        }

        .nav-title {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            padding-left: 0.75rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.6rem 0.75rem;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
            margin-bottom: 0.25rem;
        }

        .nav-link i {
            width: 20px;
            text-align: center;
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }

        .nav-link:hover {
            background-color: var(--primary-light);
            color: var(--primary);
        }

        .nav-link.active {
            background-color: var(--primary-light);
            color: var(--primary);
            font-weight: 700;
        }

        .user-profile {
            padding: 1.25rem;
            border-top: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* Main Content */
        .main-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .top-header {
            height: 72px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            z-index: 10;
        }

        .search-bar {
            background: var(--bg-body);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            width: 300px;
            transition: all 0.2s;
        }

        .search-bar:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(33, 150, 83, 0.1);
        }

        .search-bar input {
            background: transparent;
            border: none;
            outline: none;
            width: 100%;
            font-size: 0.9rem;
            color: var(--text-main);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .action-btn {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 1.25rem;
            cursor: pointer;
            position: relative;
            transition: color 0.2s;
        }

        .action-btn:hover {
            color: var(--primary);
        }

        .badge-dot {
            position: absolute;
            top: 0;
            right: 0;
            width: 8px;
            height: 8px;
            background-color: #ef4444;
            border-radius: 50%;
            border: 2px solid white;
        }

        .content-area {
            flex: 1;
            overflow-y: auto;
            padding: 2rem;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Buttons */
        .btn {
            padding: 0.6rem 1.25rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 4px 12px rgba(33, 150, 83, 0.2);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(33, 150, 83, 0.3);
        }

        .btn-outline {
            background-color: white;
            color: var(--text-main);
            border: 1px solid var(--border-color);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Cards */
        .card {
            background: var(--bg-card);
            border-radius: 16px;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            overflow: hidden;
        }

        .card-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .card-body {
            padding: 1.5rem;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <a href="{{ route('dashboard') }}" class="brand-header">
            <div class="brand-icon"><i class="fas fa-leaf"></i></div>
            <span class="brand-text">AgroSense</span>
        </a>

        <div style="flex: 1; overflow-y: auto;">
            <div class="nav-section">
                <div class="nav-title">Overview</div>
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-th-large"></i> Dashboard
                </a>
                <a href="{{ route('farms.index') }}" class="nav-link {{ request()->routeIs('farms.*') ? 'active' : '' }}">
                    <i class="fas fa-map-marked-alt"></i> Farm Overview
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-title">Monitoring</div>
                <a href="{{ route('sensors.index') }}" class="nav-link {{ request()->routeIs('sensors.*') ? 'active' : '' }}">
                    <i class="fas fa-microchip"></i> Sensors
                </a>
                <a href="{{ route('irrigation.index') }}" class="nav-link {{ request()->routeIs('irrigation.*') ? 'active' : '' }}">
                    <i class="fas fa-tint"></i> Irrigation
                </a>
                <a href="{{ route('alerts.index') }}" class="nav-link {{ request()->routeIs('alerts.*') ? 'active' : '' }}">
                    <i class="far fa-bell"></i> Alerts
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-title">Analytics & Intelligence</div>
                <a href="{{ route('diseases.index') }}" class="nav-link {{ request()->routeIs('diseases.*') ? 'active' : '' }}">
                    <i class="fas fa-virus"></i> Disease AI
                </a>
                <a href="{{ route('recommendations.index') }}" class="nav-link {{ request()->routeIs('recommendations.*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line"></i> Insights
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-title">Compliance</div>
                <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="far fa-file-alt"></i> Reports
                </a>
            @if(auth()->check() && auth()->user()->isAdmin())
                <a href="{{ route('admin.index') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}" style="position: relative;">
                    <i class="fas fa-shield-alt"></i> Admin Panel
                    @php $critAlerts = \App\Models\Alert::where('type', 'critical')->where('is_resolved', false)->count(); @endphp
                    @if($critAlerts > 0)
                    <span style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: #ef4444; color: white; font-size: 0.6rem; font-weight: 700; padding: 0.1rem 0.4rem; border-radius: 999px; min-width: 16px; text-align: center;">{{ $critAlerts }}</span>
                    @endif
                </a>
                @endif
            </div>
            
            <div style="padding: 1rem 1.5rem;">
                <a href="{{ route('home') }}" style="display: block; text-align: center; color: var(--primary); font-size: 0.85rem; font-weight: 600; text-decoration: none; padding: 0.5rem; border-radius: 8px; border: 1px dashed var(--primary); background: var(--primary-light);">
                    <i class="fas fa-external-link-alt mr-1"></i> View Public Site
                </a>
            </div>
        </div>

        <div class="user-profile">
            <a href="{{ route('profile.edit') }}" style="display:flex; align-items:center; gap:0.75rem; text-decoration:none; flex:1; min-width:0;">
                <img src="{{ auth()->user()->avatar_url ?? 'https://ui-avatars.com/api/?name=User&background=219653&color=fff' }}" alt="User" class="avatar">
                <div style="flex: 1; min-width: 0;">
                    <div style="font-weight: 700; font-size: 0.9rem; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ auth()->user()->name ?? 'Farm Owner' }}
                    </div>
                    <div style="font-size: 0.75rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        {{ auth()->user()->email ?? 'owner@agrosense.local' }}
                    </div>
                </div>
            </a>
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" style="background: none; border: none; color: var(--text-muted); cursor: pointer; padding: 0.5rem;" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Wrapper -->
    <main class="main-wrapper">
        <header class="top-header">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button id="mobileMenuBtn" style="display: none; background: none; border: none; font-size: 1.25rem; color: var(--text-main); cursor: pointer;"><i class="fas fa-bars"></i></button>
                <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0; color: var(--text-main);">@yield('breadcrumb', 'Dashboard')</h2>
            </div>
            
            <div class="header-actions">
                <form action="{{ route('search') }}" method="GET" class="search-bar">
                    <i class="fas fa-search text-gray-400" style="color: var(--text-muted);"></i>
                    <input type="text" name="q" placeholder="Search sensors, alerts..." value="{{ request('q') }}" required>
                </form>
                <button class="action-btn">
                    <i class="far fa-bell"></i>
                    <span class="badge-dot"></span>
                </button>
            </div>
        </header>

        <div class="content-area">
            <div class="dashboard-container">
                @if(session('success'))
                    <div style="background-color: #dcfce7; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; border: 1px solid #bbf7d0;">
                        <i class="fas fa-check-circle"></i>
                        <span style="font-size: 0.9rem; font-weight: 600;">{{ session('success') }}</span>
                    </div>
                @endif
                @if(session('error'))
                    <div style="background-color: #fee2e2; color: #dc2626; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.75rem; border: 1px solid #fecaca;">
                        <i class="fas fa-exclamation-circle"></i>
                        <span style="font-size: 0.9rem; font-weight: 600;">{{ session('error') }}</span>
                    </div>
                @endif
                @yield('content')
            </div>
        </div>
    </main>

    @yield('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileBtn = document.getElementById('mobileMenuBtn');
            const sidebar = document.querySelector('.sidebar');
            
            // Check if on mobile to show hamburger
            if (window.innerWidth <= 1024) {
                mobileBtn.style.display = 'block';
            }

            window.addEventListener('resize', () => {
                if (window.innerWidth <= 1024) {
                    mobileBtn.style.display = 'block';
                } else {
                    mobileBtn.style.display = 'none';
                    sidebar.classList.remove('show');
                    if (document.querySelector('.sidebar-overlay')) {
                        document.querySelector('.sidebar-overlay').classList.remove('show');
                    }
                }
            });

            // Add overlay
            const overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            document.body.appendChild(overlay);

            mobileBtn.addEventListener('click', () => {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            });

            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        });
    </script>
</body>
</html>
