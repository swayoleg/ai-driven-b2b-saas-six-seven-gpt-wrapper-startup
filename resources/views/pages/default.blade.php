@extends('layouts.site')

@section('title', $page->meta_title ?: $page->title)
@section('meta_description', $page->meta_description)

@section('main')
{!! $page->content !!}
@endsection
