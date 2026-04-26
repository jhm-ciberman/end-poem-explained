<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'End Poem Explained' }}</title>

    <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
    <link rel="shortcut icon" href="/favicon.ico" />
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
    <meta name="apple-mobile-web-app-title" content="End Poem" />
    <link rel="manifest" href="/site.webmanifest" />

    <meta name="description" content="A reading of Julian Gough's End Poem, line by line." />
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="End Poem Explained" />
    <meta property="og:title" content="End Poem Explained" />
    <meta property="og:description" content="A reading of Julian Gough's End Poem, line by line." />
    <meta property="og:url" content="{{ url()->current() }}" />
    <meta property="og:image" content="{{ url('/og.webp') }}" />
    <meta property="og:image:type" content="image/webp" />
    <meta property="og:image:width" content="1195" />
    <meta property="og:image:height" content="625" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="End Poem Explained" />
    <meta name="twitter:description" content="A reading of Julian Gough's End Poem, line by line." />
    <meta name="twitter:image" content="{{ url('/og.webp') }}" />

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

    <script defer src="https://static.cloudflareinsights.com/beacon.min.js" data-cf-beacon='{"token": "07f0479d184a46fea6ecedb1adb7037f"}'></script>
</body>
</html>
