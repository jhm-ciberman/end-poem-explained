<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'End Poem Explained' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:ital,opsz,wght@0,8..60,400;0,8..60,500;0,8..60,600;1,8..60,400;1,8..60,500&family=Inter:wght@400;500;600&family=VT323&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Restore the saved theme synchronously to avoid a light-flash on dark-mode reload. --}}
    <script>
        (function () {
            try {
                var saved = localStorage.getItem('epx-theme');
                if (saved === 'dark' || saved === 'light') {
                    document.documentElement.setAttribute('data-theme', saved);
                }
            } catch (_) {}
        })();
    </script>
</head>
<body>
    {{ $slot }}

    @livewireScripts
</body>
</html>
