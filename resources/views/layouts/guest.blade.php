<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Steph\'s Catering') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: linear-gradient(135deg, #1e293b 0%, #0f172a 50%, #1e293b 100%); min-height: 100vh; }
        .glass-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.2); }
        .input-modern { transition: all 0.2s; border: 2px solid #e2e8f0; }
        .input-modern:focus { border-color: #f97316; box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.1); outline: none; }
    </style>
</head>
<body class="font-sans text-gray-900 antialiased flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-orange-500 to-amber-500 flex items-center justify-center text-white text-2xl shadow-lg shadow-orange-500/30 mx-auto mb-4">
                <i class="fas fa-utensils"></i>
            </div>
            <h1 class="text-2xl font-bold text-white">Steph's Catering</h1>
            <p class="text-slate-400 text-sm mt-1">by Chef Nono</p>
        </div>

        <div class="glass-card rounded-3xl shadow-2xl p-8">
            {{ $slot }}
        </div>

        <p class="text-center text-slate-500 text-xs mt-6">© {{ date('Y') }} Steph's Catering. All rights reserved.</p>
    </div>
</body>
</html>