@extends('admin.layouts.app')

@section('title', 'Profile Settings')
@section('page-title', 'Profile Settings')

@section('content')
<div class="content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-md-12">

                <div class="card card-primary card-outline">

                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user-circle mr-2"></i>
                            Personal Profile
                        </h3>
                    </div>

                    <form action="{{ route('profile.update') }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="card-body">

                            {{-- Avatar --}}
                            <div class="text-center mb-4">
                                <img src="{{ auth()->user()->profile_image ?? asset('default-profile.png') }}"
                                     class="img-circle elevation-2 mb-2"
                                     width="120"
                                     height="120"
                                     alt="Avatar">

                                <div class="form-group mt-2">
                                    <label>Change Avatar</label>
                                    <div class="custom-file">
                                        <input type="file"
                                               name="profile_image"
                                               class="custom-file-input"
                                               id="profileImage">
                                        <label class="custom-file-label" for="profileImage">
                                            Choose file
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>First Name</label>
                                        <input type="text"
                                               name="fname"
                                               class="form-control"
                                               value="{{ auth()->user()->fname }}"
                                               required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Middle Name</label>
                                        <input type="text"
                                               name="mname"
                                               class="form-control"
                                               value="{{ auth()->user()->mname }}">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Last Name</label>
                                        <input type="text"
                                               name="lname"
                                               class="form-control"
                                               value="{{ auth()->user()->lname }}"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Email</label>
                                <input type="email"
                                       name="email"
                                       class="form-control"
                                       value="{{ auth()->user()->email }}"
                                       required>
                            </div>

                            <hr>

                            {{-- Change Password --}}
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-key mr-1"></i>
                                Change Password
                            </h5>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>New Password</label>
                                        <input type="password"
                                               name="password"
                                               class="form-control"
                                               placeholder="Enter new password">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Confirm Password</label>
                                        <input type="password"
                                               name="password_confirmation"
                                               class="form-control"
                                               placeholder="Confirm new password">
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="card-footer text-right">
                            <button type="submit"
                                    class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>
                                Update Profile
                            </button>
                        </div>

                    </form>

                </div>

            </div>
        </div>

    </div>
</div>
@endsection
