@extends('layouts.site')

@section('title', $post->title.' — AI driven b2b SAAS six-seven GPT-wrapper startup')
@section('meta_description', $post->excerpt)

@section('main')
<main class="shell pt-16 pb-8">
  <a href="{{ loc_url('blog') }}" class="btn btn-ghost" style="padding-left:0">{{ __('← Field Notes') }}</a>
  <article style="max-width:66ch;margin-top:20px">
    <div class="flex gap-3 items-center"><span class="tag tag-accent">{{ $post->category }}</span><span class="text-muted mono" style="font-size:12px">{{ $post->published_at->isoFormat('D MMMM YYYY') }} · {{ $post->read_minutes }} {{ __('min') }}</span></div>
    <h1 class="mt-4" style="font-size:clamp(30px,4.4vw,46px)">{{ $post->title }}</h1>
    <p class="text-muted mt-4" style="font-size:19px;line-height:1.55">{{ $post->excerpt }}</p>
    <hr class="rule" style="margin:32px 0">
    <div class="prose">
{!! $post->body !!}
    </div>
    <hr class="rule" style="margin:36px 0 20px">
    <div class="flex flex-wrap gap-4 justify-between items-center">
      <p class="text-muted" style="font-size:13px;margin:0;max-width:42ch">{{ __('Written by a person. Reviewed by a model. Approved by neither.') }}</p>
      <a href="{{ loc_url('support') }}" class="btn btn-secondary">{{ __('Buy the author a coffee') }}</a>
    </div>
  </article>
  @if($next)
  <hr class="rule" style="margin:48px 0 24px">
  <span class="eyebrow">{{ __('Next') }}</span>
  <a href="{{ loc_url('blog/'.$next->slug) }}" class="link-quiet block mt-2"><h3 style="max-width:28ch">{{ $next->title }}</h3></a>
  @endif
</main>
@endsection
