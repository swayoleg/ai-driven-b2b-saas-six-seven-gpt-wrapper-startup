@extends('layouts.site')

@section('title', $page->meta_title ?: $page->title)
@section('meta_description', $page->meta_description)
@section('og_title', $page->meta_title ?: $page->title)

@push('schema')
{!! \App\Support\Schema::render(
    {{-- concat, not prepend: prepend would mutate $posts and duplicate the featured card below --}}
    \App\Support\Schema::blog($page, $featured ? collect([$featured])->concat($posts) : $posts),
    \App\Support\Schema::breadcrumbs([__('Platform') => loc_url('/'), $page->title => loc_url('blog')])
) !!}
@endpush

@section('main')
<main class="shell pt-20">
  <span class="eyebrow" data-reveal>{{ $page->eyebrow }}</span>
  <h1 class="mt-3" data-reveal data-reveal-delay="60" style="max-width:22ch">{{ $page->title }}</h1>
  <p class="text-muted mt-4" data-reveal data-reveal-delay="120" style="font-size:17px;max-width:58ch">{{ $page->lead }}</p>
  <hr class="rule" style="margin:36px 0 0">
  @if($featured)
  <a href="{{ loc_url('blog/'.$featured->slug) }}" class="link-quiet block py-10" data-reveal>
    <div class="grid gap-8" style="grid-template-columns:repeat(auto-fit,minmax(280px,1fr))">
      <div><div class="flex gap-3 items-center"><span class="tag tag-accent">{{ $featured->category }}</span><span class="text-muted mono" style="font-size:12px">{{ $featured->published_at->isoFormat('D MMMM YYYY') }} · {{ $featured->read_minutes }} {{ __('min') }}</span></div>
      <h2 class="mt-4" style="font-size:34px">{{ $featured->title }}</h2></div>
      <div class="flex flex-col justify-center"><p class="text-muted" style="font-size:17px;line-height:1.6">{{ $featured->excerpt }}</p><span style="color:var(--color-accent);font-size:14px">{{ __('Read the note →') }}</span></div>
    </div>
  </a>
  <hr class="rule" style="margin:0 0 40px">
  @endif
  <div class="grid gap-6 pb-6" style="grid-template-columns:repeat(auto-fit,minmax(280px,1fr))">
    @foreach($posts as $i => $post)
    <a href="{{ loc_url('blog/'.$post->slug) }}" class="card elev-sm link-quiet" data-reveal @if($i % 3) data-reveal-delay="{{ ($i % 3) * 90 }}" @endif style="padding:22px"><div class="flex gap-3 items-center"><span class="tag tag-neutral">{{ $post->category }}</span><span class="text-muted mono" style="font-size:11px">{{ $post->read_minutes }} {{ __('min') }}</span></div><h3 class="card-title mt-1">{{ $post->title }}</h3><p class="card-body">{{ $post->excerpt }}</p><div class="card-meta">{{ $post->published_at->isoFormat('D MMMM YYYY') }}</div></a>
    @endforeach
  </div>
  {!! $page->content !!}
</main>
@endsection
