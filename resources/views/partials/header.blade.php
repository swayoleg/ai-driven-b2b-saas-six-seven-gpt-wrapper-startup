@php
    $locale = app()->getLocale();

    // Defined once and rendered twice (desktop bar + mobile panel) so the two
    // can never drift apart.
    $nav = [
        ['url' => loc_url('/'), 'label' => __('Platform'), 'active' => request()->routeIs('home', 'uk.home')],
        ['url' => loc_url('pricing'), 'label' => __('Pricing'), 'active' => request()->is('pricing', 'uk/pricing')],
        ['url' => loc_url('blog'), 'label' => __('Field Notes'), 'active' => request()->routeIs('blog', 'uk.blog', 'post', 'uk.post')],
        ['url' => loc_url('about'), 'label' => __('Company'), 'active' => request()->is('about', 'uk/about')],
        ['url' => loc_url('support'), 'label' => __('Support the joke'), 'active' => request()->is('support', 'uk/support')],
    ];
@endphp
<header class="sticky top-0 z-40" style="background:color-mix(in srgb, var(--color-bg) 90%, transparent); backdrop-filter: blur(10px);" x-data="{ open: false }" @keydown.escape.window="open = false">
  <div class="shell">
    <div class="flex items-center gap-4" style="padding:12px 0">
      <a href="{{ loc_url('/') }}" class="link-quiet flex items-center gap-3" style="margin-right:auto">
        <span class="mono" style="display:inline-grid;place-items:center;width:30px;height:30px;flex:none;border:1px solid var(--color-accent);border-radius:var(--radius-sm);font-size:12px;color:var(--color-accent)">67</span>
        <span class="whitespace-normal md:whitespace-nowrap" style="font-family:var(--font-heading);font-weight:500;font-size:14px;line-height:1.25">AI driven b2b SAAS six-seven GPT-wrapper startup</span>
      </a>
      <nav class="hidden md:flex items-center gap-5" style="font-size:14px" aria-label="{{ __('Primary') }}">
        @foreach($nav as $item)
        <a href="{{ $item['url'] }}" @if($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a>
        @endforeach
      </nav>
      @if(config('site.uk_enabled'))
      <div class="flex items-center gap-1" style="font-size:12px">
        <a href="{{ $locale === 'en' ? '#' : alt_locale_url('en') }}" class="btn btn-ghost" style="font-size:12px;padding:2px 6px;color:var({{ $locale === 'en' ? '--color-accent' : '--color-neutral-400' }}){{ $locale === 'en' ? ';text-decoration:underline' : '' }}">EN</a>
        <span class="text-muted">/</span>
        <a href="{{ $locale === 'uk' ? '#' : alt_locale_url('uk') }}" class="btn btn-ghost" style="font-size:12px;padding:2px 6px;color:var({{ $locale === 'uk' ? '--color-accent' : '--color-neutral-400' }}){{ $locale === 'uk' ? ';text-decoration:underline' : '' }}">УКР</a>
      </div>
      @endif
      <a href="{{ loc_url('contact') }}" class="btn btn-primary hidden sm:inline-flex">{{ __('Request access') }}</a>

      {{-- Below md the nav above is hidden, so this is the only way to reach it.
           aria-expanded is bound rather than static: a screen reader has to be
           told the panel opened, not just that a button exists. --}}
      <button type="button" class="md:hidden btn btn-ghost" style="padding:6px;line-height:0;flex:none"
              x-on:click="open = ! open"
              x-bind:aria-expanded="open ? 'true' : 'false'"
              aria-controls="mobile-nav"
              aria-label="{{ __('Menu') }}">
        <svg x-show="!open" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
        <svg x-show="open" x-cloak width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
      </button>
    </div>

    <nav id="mobile-nav" x-show="open" x-cloak x-transition.opacity.duration.150ms
         class="md:hidden flex flex-col" aria-label="{{ __('Menu') }}"
         style="gap:2px;padding:4px 0 14px">
      @foreach($nav as $item)
      <a href="{{ $item['url'] }}" @if($item['active']) aria-current="page" @endif
         x-on:click="open = false"
         style="padding:11px 2px;font-size:16px;border-bottom:1px solid var(--color-divider)">{{ $item['label'] }}</a>
      @endforeach
      <a href="{{ loc_url('contact') }}" class="btn btn-primary sm:hidden" style="margin-top:12px">{{ __('Request access') }}</a>
    </nav>

    <hr class="rule" style="margin:0">
  </div>
</header>
