<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Business Management System</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --sidebar-width: 280px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            max-width: 100%;
            overflow-x: hidden;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            overflow-y: auto;
            z-index: 1000;
            padding: 20px 0;
        }
        
        .sidebar .brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar .brand h5 {
            margin: 0;
            font-weight: 700;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            border-left-color: white;
        }
        
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.2);
            border-left-color: white;
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            min-width: 0;
        }
        
        .topbar {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 30px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .topbar-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .notification-menu {
            width: 350px;
        }
        
        .content {
            padding: 30px;
        }

        .mobile-menu-toggle,
        .sidebar-close,
        .sidebar-backdrop {
            display: none;
        }
        
        .breadcrumb {
            background-color: transparent;
            padding: 0;
            margin-bottom: 20px;
        }
        
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            border-radius: 8px 8px 0 0;
            padding: 20px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .sidebar {
                width: min(var(--sidebar-width), 86vw);
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                box-shadow: 4px 0 18px rgba(0, 0, 0, 0.18);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }

            .sidebar-close {
                display: block;
                position: absolute;
                top: 14px;
                right: 14px;
                color: white;
                background: transparent;
                border: 0;
                font-size: 1.25rem;
            }

            .sidebar-backdrop.show {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.4);
                z-index: 999;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .content {
                padding: 15px;
            }

            .topbar {
                padding: 12px 15px;
            }

            .topbar h4 {
                font-size: 1.1rem;
            }

            .topbar-right,
            .topbar-user {
                gap: 6px;
            }

            .notification-menu {
                width: min(350px, calc(100vw - 30px));
            }

            .topbar-user > div:not(.dropdown) {
                display: none;
            }

            .topbar-user img {
                width: 34px;
                height: 34px;
            }

            .card-header {
                padding: 15px;
            }

            .card-body {
                overflow-wrap: anywhere;
            }

            .table-responsive {
                border: 0;
            }

            .table {
                min-width: 600px;
            }

            .table-responsive .table {
                margin-bottom: 0;
            }

            .btn {
                white-space: normal;
            }

            .form-control,
            .form-select {
                min-width: 0;
            }
        }
    </style>
    
    @yield('css')
</head>
<body>
    <!-- Sidebar -->
    @include('layouts.sidebar')
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        @include('layouts.topbar')
        
        <!-- Content -->
        <div class="content">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @yield('content')
        </div>
    </div>

    <div class="sidebar-backdrop" aria-hidden="true"></div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (() => {
            const sidebar = document.querySelector('.sidebar');
            const backdrop = document.querySelector('.sidebar-backdrop');
            const toggle = document.querySelector('.mobile-menu-toggle');
            const close = document.querySelector('.sidebar-close');

            const setSidebar = (open) => {
                sidebar?.classList.toggle('show', open);
                backdrop?.classList.toggle('show', open);
                document.body.classList.toggle('sidebar-open', open);
            };

            toggle?.addEventListener('click', () => setSidebar(true));
            close?.addEventListener('click', () => setSidebar(false));
            backdrop?.addEventListener('click', () => setSidebar(false));
            sidebar?.querySelectorAll('a:not(.disabled)').forEach((link) => {
                link.addEventListener('click', () => setSidebar(false));
            });
        })();
    </script>
    @yield('js')
</body>
</html>