@extends('layouts.guest')

@section('title', 'Sign in')

@section('content')
<h5 class="fw-700 mb-1">Welcome back</h5>
<p class="text-muted-c small mb-4">Sign in to continue to your dashboard.</p>

<form method="POST" action="{{ route('login.store') }}">
  @csrf
  <div class="mb-3">
    <label class="form-label">Email Address</label>
    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="you@company.com" required autofocus>
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
  <div class="mb-3">
    <label class="form-label">Password</label>
    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
  </div>
  <div class="form-check mb-4">
    <input class="form-check-input" type="checkbox" name="remember" id="remember">
    <label class="form-check-label small" for="remember">Remember me</label>
  </div>
  <button type="submit" class="btn btn-primary-c w-100">Sign In</button>
</form>
@endsection
