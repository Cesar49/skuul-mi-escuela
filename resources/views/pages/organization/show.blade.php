@extends('layouts.app', ['breadcrumbs' => [
['href' => route('dashboard'), 'text' => 'Dashboard'],
['href' => route('organizations.index'), 'text' => 'Organizaciones'],
['href' => route('organizations.show', $organization), 'text' => $organization->name, 'active'],
]])

@section('title', $organization->name)

@section('page_heading', $organization->name)

@section('content')
<april:card>
  <slot:title>{{ $organization->name }}</slot:title>
  <slot:description>{{ $organization->code }}</slot:description>
  <slot:content class="space-y-6">
    <div class="flex flex-wrap justify-start gap-2">
      @can('viewReports', $organization)
      <april:button-link href="{{ route('organizations.dashboard', $organization) }}" variant="outline">Ver Organizacion</april:button-link>
      @endcan
      @can('manageMembers', $organization)
      <april:button-link href="{{ route('organizations.members.index', $organization) }}" variant="outline">Miembros</april:button-link>
      @endcan
      @can('manageDomains', $organization)
      <april:button-link href="{{ route('organizations.domains.index', $organization) }}" variant="outline">Direcciones web</april:button-link>
      @endcan
      @can('manageDomains', $organization)
      <april:button-link href="{{ route('organizations.billing-groups.index', $organization) }}" variant="outline">Facturación</april:button-link>
      @endcan
      @can('manageCampuses', $organization)
      <april:button-link href="{{ route('organizations.boarding-residences.index', $organization) }}" variant="outline">Residencias</april:button-link>
      @endcan
      @can('manageCalendar', $organization)
      <april:button-link href="{{ route('organizations.calendar-templates.index', $organization) }}" variant="outline">Calendarios</april:button-link>
      @endcan
      @can('update', $organization)
      <april:button-link href="{{ route('organizations.edit', $organization) }}">Configuración</april:button-link>
      @endcan
    </div>
    <div>
      <h2 class="font-semibold">Campuses</h2>
      <p class="mt-1 text-sm text-muted-foreground">Un administrador de la organización puede gestionar la configuración del campus. Para el acceso operativo, sigue siendo necesaria una afiliación a la institución educativa y un rol dentro de la misma..</p>
    </div>
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
      @forelse ($organization->schools as $school)
      @can('update', $school)
      <a href="{{ route('schools.edit', $school) }}" class="rounded-lg border p-5 transition hover:bg-muted/50">
        <p class="font-semibold">{{ $school->name }}</p>
        <p class="text-sm text-muted-foreground">{{ $school->address ?: '—' }}</p>
      </a>
      @else
      <div class="rounded-lg border p-5">
        <p class="font-semibold">{{ $school->name }}</p>
        <p class="text-sm text-muted-foreground">{{ $school->address ?: '—' }}</p>
      </div>
      @endcan
      @empty
      <p class="text-muted-foreground">No hay Campus agregados.</p>
      @endforelse
    </div>
    @can('createForOrganization', [\App\Models\School::class, $organization])
    <april:button-link href="{{ route('schools.create') }}" class="h-11 select-none">Agregar Campus</april:button-link>
    @endcan
  </slot:content>
</april:card>
@endsection