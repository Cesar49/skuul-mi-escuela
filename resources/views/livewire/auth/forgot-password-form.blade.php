<div class="space-y-6">
  <form action="{{ route('password.email') }}" method="POST" class="grid gap-5" x-data="{ submitting: false }" x-on:submit="submitting = true">
    @csrf

    <april:input-group name="email" id="email" type="email" label="Correo Electronico" autocomplete="email" autofocus required />
    <april:button type="submit" class="w-full justify-center" x-bind:disabled="submitting" x-bind:aria-busy="submitting">
      <span x-show="! submitting">Recuperar Contraseña</span>
      <span x-show="submitting" x-cloak>Enviando...</span>
    </april:button>
  </form>

  <p class="text-center text-sm text-muted-foreground">
    Recordaste tu contraseña?
    <a href="{{ route('login') }}" class="font-medium text-foreground underline-offset-4 hover:underline" aria-label="Login">
      Regresar al login
    </a>
  </p>
</div>