<april:card>
  <slot:title>Escuelas</slot:title>
  <slot:description>Escuelas en la plataforma.</slot:description>
  <slot:content>
    <div wire:key="{{ $id }}-{{ $this->tableRevision }}"><april:data-table id="{{ $id }}" :data="$data" :columns="$columns" :pagination="$pagination" :per-page-options="$perPageOptions" row-key="{{ $rowKey }}" :searchable="$searchable" @query-change="$wire.updateTable($event.detail)">
        <slot:empty>
          <div class="space-y-1">
            <p class="font-medium text-foreground">No hay escuelas</p>
            <p>Añade el primer campus para iniciar las actividades escolares..</p>
          </div>
        </slot:empty>
        <slot:actions>
          <x-table-actions :items="array_filter([
        ['label' => 'Ver Escuela', 'icon' => 'eye', 'url' => 'view_url'],
        ['label' => 'Editar Escuela', 'icon' => 'settings', 'url' => 'edit_url', 'when' => 'row.can_edit'],
        ['label' => 'Borrar Escuela', 'icon' => 'trash-2', 'url' => 'delete_url', 'type' => 'delete', 'confirm' => 'Borrar :name? Todo lo que contiene va con ello.', 'names' => 'row.name', 'when' => 'row.can_delete'],
    ])" />
        </slot:actions>
      </april:data-table></div>
  </slot:content>
</april:card>