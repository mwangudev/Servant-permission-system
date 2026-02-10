<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Permission Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- AdminLTE 3 CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        .register-logo b {
            color: #6366F1;
        }
    </style>
</head>

<body class="hold-transition register-page">

<div class="register-box">

    {{-- Logo --}}
    <div class="register-logo">
        <b>PMS</b> Registration
    </div>

    {{-- Card --}}
    <div class="card shadow">
        <div class="card-body register-card-body">
            <p class="login-box-msg">Create a new account</p>

            {{-- Errors --}}
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

            {{-- Registration Form --}}
            <form action="{{ route('register.submit') }}" method="POST">
                @csrf

                {{-- First Name --}}
                <div class="input-group mb-3">
                    <input type="text"
                           name="fname"
                           class="form-control"
                           placeholder="First Name"
                           value="{{ old('fname') }}"
                           required autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>

                {{-- Middle Name --}}
                <div class="input-group mb-3">
                    <input type="text"
                           name="mname"
                           class="form-control"
                           placeholder="Middle Name (optional)"
                           value="{{ old('mname') }}">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user-tag"></span>
                        </div>
                    </div>
                </div>

                {{-- Last Name --}}
                <div class="input-group mb-3">
                    <input type="text"
                           name="lname"
                           class="form-control"
                           placeholder="Last Name"
                           value="{{ old('lname') }}"
                           required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                </div>

                {{-- Email --}}
                <div class="input-group mb-3">
                    <input type="email"
                           name="email"
                           class="form-control"
                           placeholder="Email"
                           value="{{ old('email') }}"
                           required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                </div>

                {{-- Gender --}}
                <div class="input-group mb-3">
                    <select name="gender" class="form-control" required>
                        <option value="">Select Gender</option>
                        <option value="male" {{ old('gender')=='male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender')=='female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender')=='other' ? 'selected' : '' }}>Other</option>
                    </select>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-venus-mars"></span>
                        </div>
                    </div>
                </div>

                {{-- Date of Birth --}}
                <div class="input-group mb-3">
                    <input type="date"
                           name="dob"
                           class="form-control"
                           placeholder="Date of Birth"
                           value="{{ old('dob') }}"
                           required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-calendar-alt"></span>
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

                {{-- Confirm Password --}}
                <div class="input-group mb-3">
                    <input type="password"
                           name="password_confirmation"
                           class="form-control"
                           placeholder="Confirm Password"
                           required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">Register</button>
                    </div>
                </div>
            </form>

            <p class="mt-3 mb-0 text-center">
                <a href="{{ route('login') }}" class="text-center">I already have an account</a>
            </p>
        </div>
    </div>
</div>

{{-- REQUIRED SCRIPTS --}}
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

</body>
</html>
