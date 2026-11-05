
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'QECH Attendance System') | QECH</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        html, body {
            max-width: 100%;
            min-height: 100%;
            overflow-x: hidden;
        }

        body {
            font-family: Inter, ui-sans-serif, system-ui, -apple-system,
                         BlinkMacSystemFont, "Segoe UI", sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        button, a, input, select, textarea {
            transition: color .18s ease, background-color .18s ease,
                        border-color .18s ease, box-shadow .18s ease;
        }

        button:focus-visible, a:focus-visible,
        input:focus-visible, select:focus-visible {
            outline: 2px solid #6366f1;
            outline-offset: 2px;
        }

        .qech-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .035);
        }

        .qech-table-wrap {
            overflow-x: auto;
            width: 100%;
        }

        .qech-table-wrap table {
            min-width: 650px;
        }

        @media (max-width: 640px) {
            .qech-page-content {
                padding: 16px;
            }
        }
    </style>
</head>

<body class="bg-[#f3f4f6] text-gray-800 min-h-screen">

    {{-- Mobile sidebar overlay --}}
    <div id="sidebarOverlay"
         class="fixed inset-0 bg-gray-950/60 backdrop-blur-[2px]
                z-40 hidden lg:hidden"
         onclick="closeSidebar()">
    </div>

    <div class="min-h-screen">

        {{-- Existing sidebar; we will modernize its styling next --}}
        @include('partials.nav')

        {{-- Main application area --}}
        <div class="lg:ml-64 min-h-screen flex flex-col min-w-0">

            {{-- Top navigation --}}
            <header class="sticky top-0 z-30 bg-white
                           border-b border-gray-200
                           px-4 sm:px-6 lg:px-8 py-3">

                <div class="flex items-center justify-between gap-4 min-h-10">

                    {{-- Page title and mobile menu --}}
                    <div class="flex items-center gap-3 min-w-0">

                        <button type="button"
                                onclick="openSidebar()"
                                class="lg:hidden flex-shrink-0 w-10 h-10
                                       rounded-xl bg-gray-100 text-gray-700
                                       flex items-center justify-center
                                       hover:bg-gray-200"
                                aria-label="Open navigation menu">

                            <svg xmlns="http://www.w3.org/2000/svg"
                                 class="w-5 h-5" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round"
                                      stroke-linejoin="round"
                                      stroke-width="2"
                                      d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>

                        <div class="min-w-0">
                            <h1 class="text-base sm:text-lg font-semibold
                                       text-gray-900 truncate">
                                @yield('title', 'Dashboard')
                            </h1>

                            <p class="hidden sm:block text-xs text-gray-500 mt-0.5">
                                Queen Elizabeth Central Hospital
                            </p>
                        </div>
                    </div>

                    {{-- User details and logout --}}
                    <div class="flex items-center gap-2 sm:gap-3 flex-shrink-0">

                        <div class="hidden md:flex items-center gap-3
                                    border-r border-gray-200 pr-4">

                            <div class="w-9 h-9 rounded-full bg-indigo-100
                                        text-indigo-700 flex items-center
                                        justify-center font-semibold text-sm">
                                {{ strtoupper(substr(auth()->user()->username ?? 'U', 0, 1)) }}
                            </div>

                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-800
                                          truncate max-w-[160px]">
                                    {{ auth()->user()->username ?? 'User' }}
                                </p>

                                <p class="text-xs text-gray-500 truncate max-w-[160px]">
                                    {{ auth()->user()->role->name ?? '' }}
                                </p>
                            </div>
                        </div>

                        <form method="POST" action="{{ url('/logout') }}">
                            @csrf

                            <button type="submit"
                                    class="text-sm font-medium text-gray-600
                                           hover:text-red-600
                                           border border-gray-200
                                           hover:border-red-200
                                           hover:bg-red-50
                                           px-3 sm:px-4 py-2 rounded-xl">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            {{-- Flash messages --}}
            <div class="px-4 sm:px-6 lg:px-8 pt-4">
                @if (session('status'))
                    <div class="bg-white border border-green-200
                                text-green-800 px-4 py-3 rounded-xl
                                mb-2 text-sm shadow-sm">
                        <div class="flex items-start gap-2">
                            <span class="font-bold text-green-600">✓</span>
                            <span>{{ session('status') }}</span>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="bg-white border border-red-200
                                text-red-700 px-4 py-3 rounded-xl
                                mb-3 text-sm shadow-sm">
                        <p class="font-semibold mb-1">
                            Please check the following:
                        </p>
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- Page content --}}
            <main class="qech-page-content flex-1
                         px-4 sm:px-6 lg:px-8
                         py-5 sm:py-7 min-w-0">

                <div class="w-full max-w-[1600px] mx-auto">
                    @yield('content')
                </div>

            </main>

            {{-- Footer --}}
            <footer class="px-4 sm:px-6 lg:px-8 py-4
                           border-t border-gray-200 bg-white">
                <div class="max-w-[1600px] mx-auto
                            flex flex-col sm:flex-row
                            justify-between gap-1
                            text-xs text-gray-500">
                    <span>QECH Digital Duty Roster & Attendance System</span>
                    <span>Queen Elizabeth Central Hospital · Malawi</span>
                </div>
            </footer>

        </div>
    </div>

    {{-- Mobile sidebar controls --}}
    <script>
        function openSidebar() {
            const sidebar = document.getElementById('mobileSidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if (!sidebar || !overlay) return;

            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeSidebar() {
            const sidebar = document.getElementById('mobileSidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if (sidebar) {
                sidebar.classList.add('-translate-x-full');
            }

            if (overlay) {
                overlay.classList.add('hidden');
            }

            document.body.classList.remove('overflow-hidden');
        }

        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1024) {
                closeSidebar();
            }
        });
    </script>

</body>
</html>
