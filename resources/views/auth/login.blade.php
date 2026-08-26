@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-lg border-0 rounded-3">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-primary">Business System</h2>
                        <p class="text-muted">Management & Analytics</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Error!</strong>
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email Address</label>
                            <input 
                                type="email" 
                                class="form-control form-control-lg @error('email') is-invalid @enderror" 
                                id="email" 
                                name="email" 
                                value="{{ old('email') }}" 
                                required 
                                placeholder="your@email.com"
                            >
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Password</label>
                            <input 
                                type="password" 
                                class="form-control form-control-lg @error('password') is-invalid @enderror" 
                                id="password" 
                                name="password" 
                                required 
                                placeholder="••••••••"
                            >
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input 
                                class="form-check-input" 
                                type="checkbox" 
                                id="remember" 
                                name="remember"
                            >
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold">
                            Sign In
                        </button>
                    </form>

                    <hr class="my-4">

                    <p class="text-center text-muted">
                        <a href="{{ route('password.request') }}" class="text-decoration-none">
                            Forgot password?
                        </a>
                    </p>
                </div>
            </div>

            <p class="text-center text-muted mt-4 small">
                Demo Credentials:<br>
                <strong>Email:</strong> superadmin@business.local<br>
                <strong>Password:</strong> password123
            </p>
        </div>
    </div>
</div>
@endsection

<style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
</style>