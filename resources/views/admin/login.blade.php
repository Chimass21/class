<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Cfschool</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-2xl mx-auto shadow-lg shadow-indigo-500/30">CP</div>
            <h1 class="text-2xl font-bold text-white mt-4">Admin Console</h1>
            <p class="text-sm text-slate-400 mt-1">Cfschool Administration</p>
        </div>

        <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl p-8 shadow-2xl">
            <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-5">
                @csrf

                @if($errors->any())
                    <div class="p-3 bg-red-500/10 border border-red-500/20 rounded-xl text-red-400 text-sm font-medium text-center">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div>
                    <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1.5">Username or Email</label>
                    <input type="text" name="login" value="{{ old('login') }}" required autofocus
                        class="w-full px-4 py-3 bg-slate-800/60 border border-slate-600/50 rounded-xl text-sm text-white placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 transition cursor-text">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-1.5">Password</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 bg-slate-800/60 border border-slate-600/50 rounded-xl text-sm text-white placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 transition cursor-text">
                </div>

                <button type="submit" class="w-full py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold text-sm rounded-xl transition shadow-lg shadow-indigo-600/20 cursor-pointer active:scale-[0.98]">
                    Sign In
                </button>

                <p class="text-center text-xs text-slate-500">
                    <a href="{{ route('landing') }}" class="text-indigo-400 hover:text-indigo-300 transition">&larr; Back to Portal</a>
                </p>
            </form>
        </div>

        <p class="text-center text-xs text-slate-600 mt-6">&copy; {{ date('Y') }} Cfschool. All rights reserved.</p>
    </div>

</body>
</html>