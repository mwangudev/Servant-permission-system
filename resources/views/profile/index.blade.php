@extends('admin.layouts.app')

@section('title', 'Profile Settings')
@section('page-title', 'Profile Settings')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow rounded-4 border-0">
                <div class="card-header bg-gradient-primary text-white rounded-top-4">
                    <h4 class="mb-0"><i class="fas fa-user-circle me-2"></i> Personal Profile</h4>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-4 text-center">
                            <img src="{{ auth()->user()->profile_image ?? '/default-profile.png' }}" class="rounded-circle shadow border border-3 border-primary mb-2" width="120" height="120" alt="Avatar">
                            <div class="mt-2">
                                <label class="form-label">Change Avatar</label>
                                <input type="file" name="profile_image" class="form-control form-control-sm mx-auto" style="max-width: 250px;">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control rounded-pill" value="{{ auth()->user()->full_name }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control rounded-pill" value="{{ auth()->user()->email }}" required>
                        </div>
                        <hr class="my-4">
                        <h6 class="mb-3 text-primary"><i class="fas fa-key me-1"></i> Change Password</h6>
                        <div class="mb-3">
                            <input type="password" name="password" class="form-control rounded-pill" placeholder="New Password">
                        </div>
                        <div class="mb-3">
                            <input type="password" name="password_confirmation" class="form-control rounded-pill" placeholder="Confirm New Password">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 rounded-pill">Update Profile</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
