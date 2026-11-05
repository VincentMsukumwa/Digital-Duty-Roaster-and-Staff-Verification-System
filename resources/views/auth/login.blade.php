<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — QECH Attendance System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-blue-100 px-4">

    <div class="w-full max-w-sm">

        <div class="flex items-center justify-center gap-2 mb-6">
            <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white font-bold">Q</div>
            <span class="text-gray-800 font-semibold text-lg">QECH</span>
        </div>

        <div class="bg-white shadow-xl shadow-blue-100/60 rounded-2xl p-8 border border-blue-50">
            <h1 class="text-xl font-bold text-gray-800 mb-1">Welcome back</h1>
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
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <input type="password" name="password" required
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <button type="submit"
                        class="w-full bg-blue-600 text-white text-sm font-semibold py-3 rounded-full hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                    Sign In
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">
            Queen Elizabeth Central Hospital &middot; Digital Duty Roster System
        </p>
    </div>

</body>
</html>
