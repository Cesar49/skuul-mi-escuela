@extends('layouts.app', ['breadcrumbs' => array_values(array_filter([
['href'=> route('dashboard'), 'text'=> 'Dashboard'],
auth()->user()->can('viewAny', App\Models\School::class) ? ['href'=> route('schools.index'), 'text'=> 'Escuelas'] : null,
['href'=> route('schools.create'), 'text'=> 'Crear Escuela', 'active'],
]))])

@section('title', __('Crear Escuela'))

@section('page_heading', __('Crear Escuela'))

@section('content' )
@livewire('create-school-form')
@endsection