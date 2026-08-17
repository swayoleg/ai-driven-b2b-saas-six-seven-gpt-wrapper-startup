<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', config('app.name'))</title>
<meta name="description" content="@yield('meta_description')">
<link rel="canonical" href="{{ url()->current() }}">

{{-- Sharing. og:image is absolute via asset(), so it follows APP_URL per environment. --}}
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:type" content="@yield('og_type', 'website')">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="@yield('og_title', config('app.name'))">
<meta property="og:description" content="@yield('meta_description')">
<meta property="og:locale" content="{{ app()->getLocale() === 'uk' ? 'uk_UA' : 'en_GB' }}">
<meta property="og:image" content="{{ asset('uploads/og-image.jpg') }}">
<meta property="og:image:type" content="image/jpeg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="A shawarma wrapped in branded paper reading THE GPT WRAPPER — powered by OpenAI, freshly wrapped chat">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="@yield('og_title', config('app.name'))">
<meta name="twitter:description" content="@yield('meta_description')">
<meta name="twitter:image" content="{{ asset('uploads/og-image.jpg') }}">
<meta name="twitter:image:alt" content="A shawarma wrapped in branded paper reading THE GPT WRAPPER">

{!! \App\Support\Schema::render(\App\Support\Schema::site()) !!}
@stack('schema')
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icon-192.png') }}">
<link rel="icon" type="image/png" sizes="512x512" href="{{ asset('icon-512.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
<meta name="theme-color" content="#161826">
{{-- Everything below is built by gulp and served from this origin. Tailwind,
     Alpine and jQuery used to come off three CDNs and Inter off a fourth, via
     an @import inside the design-system sheet; that was ~2.3s of render-blocking
     requests on mobile. See gulpfile.js. --}}

{{-- The one font subset every page needs. @font-face is in the inline critical
     block below, so this only buys a round trip — but it is the round trip
     between first paint and the text being in Inter. --}}
<link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/inter-v20-latin.woff2') }}" crossorigin>

{{-- Above-the-fold CSS, inlined so the first paint needs no network at all. --}}
@include('partials.critical')

{{-- The full stylesheet, loaded without blocking the render: the browser
     fetches it at low priority as a preload, then the onload handler promotes
     it to a real stylesheet. --}}
<link rel="preload" as="style" href="{{ asset_v('assets/app.min.css') }}" onload="this.onload=null;this.rel='stylesheet'">
<noscript><link rel="stylesheet" href="{{ asset_v('assets/app.min.css') }}"></noscript>

{{-- jQuery + the site's own script + Alpine, concatenated in that order —
     app.js registers its Alpine components on `alpine:init`, so it has to be
     parsed before Alpine's runtime. --}}
<script defer src="{{ asset_v('assets/app.min.js') }}"></script>
</head>
<body class="antialiased">
@include('partials.header')
@yield('main')
@include('partials.footer')
{{-- Page-specific scripts (e.g. the wallet clipboard helper) are pushed here
     so they only load on the pages that actually need them. --}}
@stack('scripts')
@include('partials.analytics')
</body>
</html>
