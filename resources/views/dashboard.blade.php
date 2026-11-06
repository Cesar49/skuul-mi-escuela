@extends('layouts.app', ['breadcrumbs' => [
['href'=> route('dashboard'), 'text'=> 'Dashboard', 'active'],
]])

@section('title', __('Dashboard'))

@section('page_heading', 'Panel Principal')

@section('content')

@livewire('dashboard-data-cards')


@if (auth()->user()->hasRole(\App\Enums\Role::Student))
<div class="flex flex-wrap items-center justify-between gap-3 border-y py-3">
  <p class="font-medium">Tu Perfil</p>
  <april:button-link href="{{ route('students.print-profile', auth()->user()->id) }}" variant="outline">
    <x-lucide-download class="mr-2 size-4" />
    Descargar perfil
  </april:button-link>
</div>
@endif

@endsection