<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', config('app.name'))</title>
<meta name="description" content="@yield('meta_description')">
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
<link rel="stylesheet" href="{{ asset('assets/site.css') }}">
<script defer src="{{ asset('assets/app.js') }}"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="antialiased">
@include('partials.header')
@yield('main')
@include('partials.footer')
</body>
</html>
