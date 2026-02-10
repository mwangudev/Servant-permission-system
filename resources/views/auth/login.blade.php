<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Permission Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- AdminLTE 3 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        .login-logo b {
            color: #6366F1;
        }
    </style>
</head>

<body class="hold-transition login-page">

<div class="login-box">
    
    {{-- Logo --}}
    <div class="login-logo">
        <b>PMS</b> Login
    </div>

    {{-- Card --}}
    <div class="card shadow">
        <div class="card-body login-card-body">
            
            <p class="login-box-msg">Sign in to start your session</p>

            {{-- Success message --}}
            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('status') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            {{-- Error messages --}}
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            {{-- Login Form --}}
            <form action="{{ route('login.submit') }}" method="POST">
                @csrf

                {{-- Email --}}
                <div class="input-group mb-3">
                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           class="form-control"
                           placeholder="Email address"
                           required autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>

                {{-- Password --}}
                <div class="input-group mb-3">
                    <input type="password"
                           name="password"
                           class="form-control"
                           placeholder="Password"
                           required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>

                {{-- Remember Me --}}
                <div class="row">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">
                                Remember Me
                            </label>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-block">
                            Sign In
                        </button>
                    </div>
                </div>
            </form>

            {{-- Links --}}
            <p class="mb-1 mt-3">
                <a href="#" class="text-muted">Forgot password? Contact Admin</a>
            </p>
            <p class="mb-0">
                <a href="{{ route('register') }}" class="text-center">
                    Register new account
                </a>
            </p>

        </div>
    </div>
</div>

{{-- REQUIRED SCRIPTS FOR ADMINLTE 3 --}}
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

</body>
</html>
