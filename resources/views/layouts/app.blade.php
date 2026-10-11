<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'E-Handog')</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    },

                    colors: {
                        primary: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            200: '#fed7aa',
                            300: '#fdba74',
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                            800: '#9a3412',
                            900: '#7c2d12'
                        },

                        slate: {
                            850: '#1e293b'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        .glass {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow:
                0 20px 25px -5px rgba(0, 0, 0, 0.1),
                0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .sidebar-link {
            transition: all 0.2s;
            border-radius: 0.75rem;
        }

        .sidebar-link:hover,
        .sidebar-link.active {
            background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
            color: white;
            box-shadow: 0 4px 14px 0 rgba(234, 88, 12, 0.39);
        }

        .btn-primary {
            background: linear-gradient(135deg, #ea580c 0%, #f97316 100%);
            transition: all 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px -10px rgba(234, 88, 12, 0.5);
        }

        .table-row {
            transition: background 0.15s;
        }

        .table-row:hover {
            background: #fff7ed;
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .status-badge {
            font-weight: 600;
            letter-spacing: 0.025em;
        }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
        }

        .gradient-text {
            background: linear-gradient(135deg, #ea580c, #fbbf24);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .page-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        }

        .input-modern {
            transition: all 0.2s;
            border: 2px solid #f1f5f9;
        }

        .input-modern:focus {
            border-color: #f97316;
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1);
            outline: none;
        }

        @media print {
            @page {
                size: auto;
                margin: 12mm;
            }

            html,
            body {
                height: auto !important;
                overflow: visible !important;
                width: 100% !important;
            }

            * {
                overflow: visible !important;
                overflow-x: visible !important;
                overflow-y: visible !important;
            }

            nav,
            .no-print {
                display: none !important;
            }

            main,
            .max-w-7xl {
                max-width: 100% !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased">

    <div class="flex h-screen overflow-hidden">

        {{-- Sidebar --}}
        <aside class="w-64 bg-white border-r border-slate-200 flex flex-col hidden md:flex z-20">

            {{-- E-Handog Branding --}}
            <div class="p-6 border-b border-slate-100">

                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">

                    <div
                        class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-500 to-amber-500 flex items-center justify-center text-white text-lg shadow-lg shadow-orange-200">

                        <i class="fas fa-calendar-check"></i>

                    </div>

                    <div>
                        <div class="font-bold text-slate-800 leading-tight">
                            E Serve
                        </div>

                        <div class="text-[10px] text-slate-400 uppercase tracking-widest font-semibold">
                            Scheduling & Tracking
                        </div>
                    </div>

                </a>

            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto p-4 space-y-1">

                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3 px-3 mt-2">
                    Menu
                </div>

                <a href="{{ route('dashboard') }}"
                    class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-slate-600">

                    <i class="fas fa-home w-5 text-center"></i>
                    Dashboard

                </a>

                @if(auth()->user()->isAdmin())

                    <a href="{{ route('events.index') }}"
                        class="sidebar-link {{ request()->routeIs('events.*') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-slate-600">

                        <i class="fas fa-calendar-alt w-5 text-center"></i>
                        Events

                    </a>

                    <a href="{{ route('waiters.index') }}"
                        class="sidebar-link {{ request()->routeIs('waiters.*') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-slate-600">

                        <i class="fas fa-users w-5 text-center"></i>
                        Waiters

                    </a>

                    {{-- Equipment dropdown: Items, Categories, Allocations, Logs --}}
                    @php
                        $equipmentOpen = request()->routeIs(
                            'equipment-items.*',
                            'equipment-categories.*',
                            'event-equipment.*',
                            'equipment-logs.*'
                        );
                    @endphp
                    <div>
                        <button type="button" id="equipmentToggle"
                            aria-expanded="{{ $equipmentOpen ? 'true' : 'false' }}" aria-controls="equipmentSubmenu"
                            class="w-full flex items-center gap-3 px-3 py-2.5 text-sm font-medium rounded-xl transition
                                {{ $equipmentOpen ? 'text-orange-700 bg-orange-50' : 'text-slate-600 hover:bg-slate-50' }}">
                            <i class="fas fa-box w-5 text-center"></i>
                            <span class="flex-1 text-left">Equipment</span>
                            <i id="equipmentChevron"
                                class="fas fa-chevron-down text-xs transition-transform duration-200 {{ $equipmentOpen ? 'rotate-180' : '' }}"></i>
                        </button>

                        <div id="equipmentSubmenu"
                            class="{{ $equipmentOpen ? '' : 'hidden' }} mt-1 ml-4 pl-3 border-l-2 border-slate-100 space-y-1">

                            <a href="{{ route('equipment-items.index') }}"
                                class="sidebar-link {{ request()->routeIs('equipment-items.*') ? 'active' : '' }} flex items-center gap-3 px-3 py-2 text-sm font-medium text-slate-600">
                                <i class="fas fa-boxes w-4 text-center text-xs"></i>
                                Equipment Items
                            </a>

                            <a href="{{ route('equipment-categories.index') }}"
                                class="sidebar-link {{ request()->routeIs('equipment-categories.*') ? 'active' : '' }} flex items-center gap-3 px-3 py-2 text-sm font-medium text-slate-600">
                                <i class="fas fa-tags w-4 text-center text-xs"></i>
                                Categories
                            </a>

                            <a href="{{ route('event-equipment.index') }}"
                                class="sidebar-link {{ request()->routeIs('event-equipment.*') ? 'active' : '' }} flex items-center gap-3 px-3 py-2 text-sm font-medium text-slate-600">
                                <i class="fas fa-hand-holding w-4 text-center text-xs"></i>
                                Allocations
                            </a>

                            <a href="{{ route('equipment-logs.index') }}"
                                class="sidebar-link {{ request()->routeIs('equipment-logs.*') ? 'active' : '' }} flex items-center gap-3 px-3 py-2 text-sm font-medium text-slate-600">
                                <i class="fas fa-clipboard-list w-4 text-center text-xs"></i>
                                Logs
                            </a>

                        </div>
                    </div>

                    <a href="{{ route('reports.index') }}"
                        class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-slate-600">

                        <i class="fas fa-chart-pie w-5 text-center"></i>
                        Reports

                    </a>

                @else

                    <a href="{{ route('waiters.my-profile') }}"
                        class="sidebar-link {{ request()->routeIs('waiters.my-profile', 'waiters.my-event') ? 'active' : '' }} flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-slate-600">

                        <i class="fas fa-user w-5 text-center"></i>
                        My Profile

                    </a>

                @endif

            </nav>

            {{-- User Section --}}
            <div class="p-4 border-t border-slate-100">

                @php
                    // Waiters who have an uploaded photo show it here; everyone else gets their initial
                    $sidebarPhoto = auth()->user()->waiter?->photo;
                    $sidebarPhotoUrl = $sidebarPhoto ? asset('storage/' . $sidebarPhoto) : null;
                    $sidebarInitial = strtoupper(mb_substr(auth()->user()->name, 0, 1));
                @endphp

                <div class="flex items-center gap-3 mb-3">

                    @if($sidebarPhotoUrl)
                        <img src="{{ $sidebarPhotoUrl }}" alt="{{ auth()->user()->name }}"
                            class="w-9 h-9 shrink-0 rounded-full object-cover bg-slate-100 ring-2 ring-orange-100"
                            onerror="this.replaceWith(Object.assign(document.createElement('div'), { className: 'w-9 h-9 shrink-0 rounded-full bg-gradient-to-br from-slate-700 to-slate-600 flex items-center justify-center text-white text-xs font-bold', textContent: @js($sidebarInitial) }));">
                    @else
                        <div
                            class="w-9 h-9 shrink-0 rounded-full bg-gradient-to-br from-slate-700 to-slate-600 flex items-center justify-center text-white text-xs font-bold">

                            {{ $sidebarInitial }}

                        </div>
                    @endif

                    <div class="flex-1 min-w-0">

                        <div class="text-sm font-semibold text-slate-700 truncate">
                            {{ auth()->user()->name }}
                        </div>

                        <div class="text-xs text-slate-400 capitalize">
                            {{ auth()->user()->role }}
                        </div>

                    </div>

                </div>

                <form method="POST" action="{{ route('logout') }}">

                    @csrf

                    <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 transition">

                        <i class="fas fa-sign-out-alt"></i>
                        Logout

                    </button>

                </form>

            </div>

        </aside>

        {{-- Main Content --}}
        <div class="flex-1 flex flex-col overflow-hidden">

            {{-- Top Bar (mobile only: hamburger + bell). Desktop has no header to save space. --}}
            <header
                class="md:hidden glass border-b border-slate-200 h-14 flex items-center justify-between px-4 sticky top-0 z-10">

                <button class="text-slate-500 hover:text-slate-700"
                    onclick="document.querySelector('aside').classList.toggle('hidden'); document.querySelector('aside').classList.toggle('absolute'); document.querySelector('aside').classList.toggle('h-full');">

                    <i class="fas fa-bars text-xl"></i>

                </button>

                <a href="{{ route('notifications.index') }}"
                    class="relative p-2 rounded-full hover:bg-slate-100 transition text-slate-500">

                    <i class="fas fa-bell text-lg"></i>

                    @if(auth()->user()->unreadNotifications->count() > 0)

                        <span
                            class="absolute top-1 right-1 w-4 h-4 bg-red-500 rounded-full text-[10px] text-white flex items-center justify-center font-bold animate-pulse">

                            {{ auth()->user()->unreadNotifications->count() }}

                        </span>

                    @endif

                </a>

            </header>

            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto p-4">

                @if(session('success'))

                    <div
                        class="animate-fade-in mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-4 rounded-xl flex items-center gap-3 shadow-sm">

                        <i class="fas fa-check-circle text-emerald-500 text-lg"></i>

                        <span class="font-medium">
                            {{ session('success') }}
                        </span>

                    </div>

                @endif

                @if(session('error'))

                    <div
                        class="animate-fade-in mb-4 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-xl flex items-center gap-3 shadow-sm">

                        <i class="fas fa-exclamation-circle text-red-500 text-lg"></i>

                        <span class="font-medium">
                            {{ session('error') }}
                        </span>

                    </div>

                @endif

                @yield('content')

            </main>

        </div>

    </div>

    {{-- Equipment dropdown toggle --}}
    <script>
        (function () {
            const toggle = document.getElementById('equipmentToggle');
            if (!toggle) return; // waiters don't see this menu

            const submenu = document.getElementById('equipmentSubmenu');
            const chevron = document.getElementById('equipmentChevron');

            toggle.addEventListener('click', function () {
                const isOpen = !submenu.classList.contains('hidden');
                submenu.classList.toggle('hidden', isOpen);
                chevron.classList.toggle('rotate-180', !isOpen);
                toggle.setAttribute('aria-expanded', String(!isOpen));
            });
        })();
    </script>

</body>

</html>