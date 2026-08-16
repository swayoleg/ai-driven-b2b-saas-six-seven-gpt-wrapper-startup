<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', config('app.name'))</title>
<meta name="description" content="@yield('meta_description')">
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icon-192.png') }}">
<link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icon-512.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
<meta name="theme-color" content="#161826">
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    corePlugins: { preflight: false },
    theme: { extend: {
      colors: { bg: 'var(--color-bg)', surface: 'var(--color-surface)', ink: 'var(--color-text)', accent: 'var(--color-accent)', divider: 'var(--color-divider)', section: 'var(--color-section)' },
      fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
      borderRadius: { sm: 'var(--radius-sm)', md: 'var(--radius-md)', lg: 'var(--radius-lg)' },
    } }
  };
</script>
<link rel="stylesheet" href="{{ asset('_ds/nocturne-f619cc82-e259-47de-8eed-259febfc742a/styles.css') }}">
<link rel="stylesheet" href="{{ asset_v('assets/site.css') }}">
<script defer src="{{ asset_v('assets/app.js') }}"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="antialiased">
@include('partials.header')
@yield('main')
@include('partials.footer')
{{-- Page-specific scripts (e.g. the wallet clipboard helper) are pushed here
     so they only load on the pages that actually need them. --}}
@stack('scripts')
</body>
</html>
