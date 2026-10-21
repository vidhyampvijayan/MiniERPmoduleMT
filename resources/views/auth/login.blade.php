@extends('layouts.app', ['title' => 'Login'])

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-12 col-md-5 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 text-center">
                <h4 class="mb-0 fw-bold">Mini ERP</h4>
                <small class="text-muted">Sign in to your account</small>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', 'admin@minierp.test') }}" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" value="password" required>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Sign In</button>
                    </div>
                </form>
            </div>
          
        </div>
    </div>
</div>
@endsection
