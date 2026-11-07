@extends('layouts.app', ['breadcrumbs' => [
    ['href'=> route('dashboard'), 'text'=> 'Dashboard'],
    ['href'=> route('schools.index'), 'text'=> 'Escuelas' , 'active']
]])

@section('title', __('Escuelas'))

@section('page_heading', 'Escuelas')

@section('page_actions')
    <x-resource-create-action :href="route('schools.create')" ability="create" :arguments="[\App\Models\School::class]">Agregar Escuela</x-resource-create-action>
@endsection

@section('content', )
    @livewire('set-school')
    
    @livewire('list-schools-table')
@endsection
