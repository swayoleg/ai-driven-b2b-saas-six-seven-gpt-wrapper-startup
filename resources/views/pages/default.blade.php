@extends('layouts.site')

@php
    $content = $page->content;
    $hasWallets = str_contains($content, '[wallets]');

    if ($hasWallets) {
        $content = str_replace(
            '[wallets]',
            view('partials.wallets', ['wallets' => \App\Models\Wallet::activeOrdered()->get()])->render(),
            $content
        );
    }
@endphp

@section('title', $page->meta_title ?: $page->title)
@section('meta_description', $page->meta_description)
@section('og_title', $page->meta_title ?: $page->title)

@push('schema')
{!! \App\Support\Schema::render(\App\Support\Schema::webPage($page)) !!}
@endpush

@section('main')
{!! $content !!}
@endsection

@if($hasWallets)
    @push('scripts')
    <script src="{{ asset_v('assets/wallets.min.js') }}"></script>
    @endpush
@endif
