<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Exambro</title>
    <link rel="icon" type="image/webp" href="{{ asset('images/logo.webp') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.webp') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; }
    </style>
</head>
<body class="antialiased min-h-screen flex items-center justify-center p-4 selection:bg-indigo-500 selection:text-white">

    <div class="w-full max-w-md">
        <!-- Logo Area -->
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo.webp') }}" class="w-16 h-16 object-contain mx-auto mb-3" alt="Logo">
            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Admin Exambro</h1>
            <p class="text-slate-500 text-sm mt-1">Masuk ke panel kontrol admin</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white p-8 rounded-3xl shadow-xl shadow-slate-200/50 border border-slate-200/80">
            @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-rose-50/80 border border-rose-200 text-rose-700 text-sm font-medium flex items-start gap-3">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div>
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <input type="text" name="username" value="{{ old('username') }}" required class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 transition-all shadow-2xs" placeholder="Masukkan username admin">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-800 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <input type="password" name="password" required class="w-full pl-11 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 text-sm focus:outline-none focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/15 transition-all shadow-2xs" placeholder="Masukkan password admin">
                    </div>
                </div>

                <button type="submit" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-md shadow-indigo-500/25 transition-all duration-150 active:scale-[0.98] focus:outline-none focus:ring-3 focus:ring-indigo-500/30">
                    Masuk ke Dashboard
                </button>
            </form>
        </div>
        
        <div class="text-center mt-8">
            <p class="text-xs font-medium text-slate-400">Protected by Exambro Engine</p>
        </div>
    </div>

</body>
</html>
