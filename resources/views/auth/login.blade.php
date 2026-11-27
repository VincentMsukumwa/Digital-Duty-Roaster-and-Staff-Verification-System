<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — QECH Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-100 via-white to-blue-50 px-4">

    <div class="w-full max-w-sm">

        {{-- PROFESSIONAL BRANDING --}}
       <div class="flex flex-col items-center justify-center mb-7">
    <div class="w-16 h-16 rounded-2xl bg-[#0F766E] flex items-center justify-center shadow-lg shadow-teal-900/20">
        <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 11c0-1.1-.9-2-2-2s-2 .9-2 2v1c0 2.5-.5 4.5-1.5 6.5M12 7c-2.2 0-4 1.8-4 4v1c0 3-.7 5.5-2 7.5M12 4c-3.9 0-7 3.1-7 7v1c0 3.5-.8 6.5-2 8M12 11c1.1 0 2 .9 2 2v1c0 2.5-.5 4.5-1.5 6.5M12 7c2.2 0 4 1.8 4 4v1c0 3 .7 5.5 2 7.5M12 4c3.9 0 7 3.1 7 7v1c0 3.5.8 6.5 2 8"/>
        </svg>
    </div>

    <h1 class="mt-3 text-xl font-bold tracking-wide text-[#123B5D]">
        QECH ATTENDANCE
    </h1>

    <p class="mt-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-[#0F766E]">
        Digital Duty &amp; Attendance
    </p>
</div>

        <div class="bg-white shadow-xl shadow-slate-200/70 rounded-2xl p-8 border border-slate-100">
            <h2 class="text-xl font-bold text-gray-800 mb-1">Welcome back</h2>
            <p class="text-sm text-gray-500 mb-6">Sign in to the duty roster &amp; attendance system</p>

            @if ($errors->any())
                <div class="bg-red-50 text-red-700 text-sm px-4 py-2.5 rounded-xl mb-4 border border-red-100">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ url('/login') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Username</label>
                    <input type="text" name="username" value="{{ old('username') }}" required autofocus
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#123B5D] focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <input type="password" name="password" required
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#123B5D] focus:border-transparent">
                </div>

                <button type="submit"
                        class="w-full bg-[#123B5D] text-white text-sm font-semibold py-3 rounded-full hover:bg-[#0F766E] transition shadow-lg shadow-blue-900/15">
                    Sign In
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
            Queen Elizabeth Central Hospital &middot; Malawi
        </p>
        <p class="text-center text-[10px] text-gray-400 mt-1">
            Digital Duty Roster &amp; Staff Attendance System
        </p>
    </div>

</body>
</html>