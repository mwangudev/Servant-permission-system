@extends('admin.layouts.app')

@section('title', 'Account Settings')
@section('page-title', 'Account Settings')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-12">
            <div class="card shadow rounded-4 border-0">
                <div class="card-header bg-gradient-secondary text-white rounded-top-4">
                    <h4 class="mb-0"><i class="fas fa-cog me-2"></i> Account Settings</h4>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-4 text-center">
                            @if(auth()->user()->profile_image)
                                <img src="{{ auth()->user()->profile_image }}" class="rounded-circle shadow border border-3 border-secondary mb-2" width="100" height="100" alt="Avatar">
                            @endif
                            <div class="mt-2">
                                <label class="form-label">Change Avatar</label>
                                <input type="file" name="profile_image" class="form-control form-control-sm mx-auto" style="max-width: 250px;">
                            </div>
                        </div>
                            <div class="mb-3">
                                <label class="form-label">First Name</label>
                                <input type="text" name="firstname" class="form-control rounded-pill" value="{{ auth()->user()->firstname }}" required>
                            </div>
                        <div class="mb-3">
                            <label class="form-label">Middle Name</label>
                            <input type="text" name="middlename" class="form-control rounded-pill" value="{{ auth()->user()->middlename }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="lastname" class="form-control rounded-pill" value="{{ auth()->user()->lastname }}" required>
                        </div>
                        <div class="mb-3"></div>
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control rounded-pill" value="{{ auth()->user()->email }}" required>
                        </div>
                        <hr class="my-4">
                        <h6 class="mb-3 text-secondary"><i class="fas fa-key me-1"></i> Change Password</h6>
                        <div class="mb-3">
                            <input type="password" name="password" class="form-control rounded-pill" placeholder="New Password">
                        </div>
                        <div class="mb-3">
                            <input type="password" name="password_confirmation" class="form-control rounded-pill" placeholder="Confirm New Password">
                        </div>
                        <button type="submit" class="btn btn-secondary w-100 rounded-pill">Save Settings</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
