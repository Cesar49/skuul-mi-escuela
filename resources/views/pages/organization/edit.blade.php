@extends('layouts.app', ['breadcrumbs' => [
['href' => route('dashboard'), 'text' => 'Dashboard'],
['href' => route('organizations.index'), 'text' => 'Organizaciones'],
['href' => route('organizations.show', $organization), 'text' => $organization->name],
['href' => route('organizations.edit', $organization), 'text' => 'Configuracion', 'active'],
]])

@section('title', __('Configuracion Organizacion'))

@section('page_heading', __('Configuracion Organizacion'))

@section('content')
@livewire('organization-form', ['organization' => $organization])
@endsection