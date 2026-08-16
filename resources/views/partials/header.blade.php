@php($locale = app()->getLocale())
<header class="sticky top-0 z-40" style="background:color-mix(in srgb, var(--color-bg) 90%, transparent); backdrop-filter: blur(10px);">
  <div class="shell">
    <div class="flex items-center gap-4 flex-wrap" style="padding:12px 0">
      <a href="{{ loc_url('/') }}" class="link-quiet flex items-center gap-3" style="margin-right:auto">
        <span class="mono" style="display:inline-grid;place-items:center;width:30px;height:30px;flex:none;border:1px solid var(--color-accent);border-radius:var(--radius-sm);font-size:12px;color:var(--color-accent)">67</span>
        <span style="font-family:var(--font-heading);font-weight:500;font-size:14px;line-height:1.25;white-space:nowrap">AI driven b2b SAAS six-seven GPT-wrapper startup</span>
      </a>
      <nav class="hidden md:flex items-center gap-5" style="font-size:14px">
        <a href="{{ loc_url('/') }}" @if(request()->routeIs('home', 'uk.home')) aria-current="page" @endif>{{ __('Platform') }}</a>
        <a href="{{ loc_url('pricing') }}" @if(request()->is('pricing', 'uk/pricing')) aria-current="page" @endif>{{ __('Pricing') }}</a>
        <a href="{{ loc_url('blog') }}" @if(request()->routeIs('blog', 'uk.blog', 'post', 'uk.post')) aria-current="page" @endif>{{ __('Field Notes') }}</a>
        <a href="{{ loc_url('about') }}" @if(request()->is('about', 'uk/about')) aria-current="page" @endif>{{ __('Company') }}</a>
        <a href="{{ loc_url('support') }}" @if(request()->is('support', 'uk/support')) aria-current="page" @endif>{{ __('Support the joke') }}</a>
      </nav>
      <div class="flex items-center gap-1" style="font-size:12px">
        <a href="{{ $locale === 'en' ? '#' : alt_locale_url('en') }}" class="btn btn-ghost" style="font-size:12px;padding:2px 6px;color:var({{ $locale === 'en' ? '--color-accent' : '--color-neutral-400' }}){{ $locale === 'en' ? ';text-decoration:underline' : '' }}">EN</a>
        <span class="text-muted">/</span>
        <a href="{{ $locale === 'uk' ? '#' : alt_locale_url('uk') }}" class="btn btn-ghost" style="font-size:12px;padding:2px 6px;color:var({{ $locale === 'uk' ? '--color-accent' : '--color-neutral-400' }}){{ $locale === 'uk' ? ';text-decoration:underline' : '' }}">УКР</a>
      </div>
      <a href="{{ loc_url('contact') }}" class="btn btn-primary">{{ __('Request access') }}</a>
    </div>
    <hr class="rule" style="margin:0">
  </div>
</header>
