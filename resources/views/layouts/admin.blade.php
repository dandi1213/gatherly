<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gatherly Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                },
            },
        }
    </script>
</head>
<body class="bg-gray-50 font-sans">
    @stack('scripts')
    
    <nav class="border-b border-gray-100 bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <span class="text-xl font-bold text-indigo-600">Gatherly Admin</span>
                </div>
                <div class="hidden sm:flex items-center gap-6">
                    <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600">Dashboard</a>
                    <a href="{{ route('admin.users.index') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600">User</a>
                    <a href="{{ route('admin.events.index') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600">Event</a>
                    <a href="{{ route('admin.categories.index') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600">Kategori</a>
                    <div>
                        <button 
                            id="logoutBtn"
                            class="rounded-md bg-red-100 px-3 py-1 text-xs font-medium text-red-600 hover:bg-red-200"
                        >
                            Keluar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <script>
        document.getElementById('logoutBtn')?.addEventListener('click', () => {
            axios.post('/logout')
                .then(() => window.location.href = '/login')
                .catch(() => window.location.href = '/login');
        });
    </script>
</body>
</html>