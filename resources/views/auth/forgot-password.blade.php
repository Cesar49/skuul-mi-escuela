@extends('layouts.guest')

@section('title', 'Forgot password')

@section('body')
<x-partials.authentication-card title="Olvidaste tu contraseña?" description="Enviaremos un enlace de restablecimiento a tu dirección de correo electrónico..">
  <x-display-validation-errors />
  <livewire:auth.forgot-password-form />
</x-partials.authentication-card>
@endsection