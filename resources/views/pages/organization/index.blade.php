@extends('layouts.app', ['breadcrumbs' => [
['href' => route('dashboard'), 'text' => 'Dashboard'],
['href' => route('organizations.index'), 'text' => 'Organizaciones', 'active'],
]])

@section('title', __('Organizaciones'))

@section('page_heading', __('Organizaciones'))

@section('page_actions')
<x-resource-create-action :href="route('organizations.create')" ability="create" :arguments="[\App\Models\Organization::class]">Agregar Organizacion</x-resource-create-action>
@endsection

@section('content')
<april:card>
  <slot:title>Organizaciones</slot:title>
  <slot:description>Las organizaciones poseen campus. Los roles en el campus y los registros operativos permanecen circunscritos a la institución educativa en cuestión..</slot:description>
  <slot:content class="space-y-4">
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      @forelse ($organizations as $organization)
      <a href="{{ route('organizations.show', $organization) }}" class="rounded-lg border p-5 transition hover:bg-muted/50">
        <p class="font-semibold">{{ $organization->name }}</p>
        <p class="text-sm text-muted-foreground">{{ trans_choice('{1} :count campus|[0,*] :count campuses', $organization->schools_count) }}</p>
      </a>
      @empty
      <div class="flex flex-col items-start gap-3 text-muted-foreground">
        <p>No hay organizaciones disponibles para esta cuenta.</p>
        <x-resource-create-action :href="route('organizations.create')" ability="create" :arguments="[\App\Models\Organization::class]">Agregar Organizacion</x-resource-create-action>
      </div>
      @endforelse
    </div>
  </slot:content>
</april:card>
@endsection