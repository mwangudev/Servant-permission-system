@extends('admin.layouts.app')

@section('title', 'View User')
@section('page-title', 'User Details')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            {{-- User Information Card --}}
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center rounded-top-3">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i> User Information
                    </h5>
                    <div>
                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-warning me-1">
                            <i class="fas fa-edit me-1"></i> Edit
                        </a>
                        <a href="{{ route('users.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- User Details --}}
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted text-uppercase">Full Name</h6>
                            <p class="lead">{{ $user->full_name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted text-uppercase">Email</h6>
                            <p class="lead">
                                <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted text-uppercase">Gender</h6>
                            <p class="lead">{{ ucfirst($user->gender) }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted text-uppercase">Date of Birth</h6>
                            <p class="lead">
                                {{ $user->dob->format('d M, Y') }}
                                <small class="text-muted">({{ $user->dob->diffInYears(now()) }} years old)</small>
                            </p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted text-uppercase">Role</h6>
                            <span class="badge bg-info px-3 py-2">{{ ucfirst($user->role) }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted text-uppercase">Department</h6>
                            <p class="lead">
                                @if($user->department)
                                    <a href="{{ route('departments.show', $user->department->id) }}">
                                        {{ $user->department->name }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted text-uppercase">Account Status</h6>
                            @if($user->email_verified_at)
                                <span class="badge bg-success px-3 py-2">
                                    <i class="fas fa-check me-1"></i> Verified
                                </span>
                            @else
                                <span class="badge bg-warning px-3 py-2">
                                    <i class="fas fa-clock me-1"></i> Pending Verification
                                </span>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted text-uppercase">Member Since</h6>
                            <p class="lead">{{ $user->created_at->format('d M, Y') }}</p>
                        </div>
                    </div>

                    {{-- Leave Requests --}}
                    @if($user->LeaveRequests()->count() > 0)
                        <hr>
                        <h5 class="mb-3">
                            <i class="fas fa-calendar-alt me-2"></i> Leave Requests
                            <span class="badge bg-secondary">{{ $user->LeaveRequests()->count() }}</span>
                        </h5>

                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Type</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($user->LeaveRequests() as $leave)
                                        @php
                                            $statusColors = [
                                                'submitted' => 'info',
                                                'pending' => 'warning',
                                                'on_progress' => 'primary',
                                                'approved' => 'success',
                                                'rejected' => 'danger',
                                            ];
                                            $color = $statusColors[$leave->status] ?? 'secondary';
                                        @endphp
                                        <tr class="align-middle">
                                            <td>{{ ucfirst(str_replace('_', ' ', $leave->request_type)) }}</td>
                                            <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</td>
                                            <td>
                                                <span class="badge bg-{{ $color }}">
                                                    {{ ucfirst(str_replace('_', ' ', $leave->status)) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="card-footer bg-light text-end">
                    <small class="text-muted">
                        Last updated: {{ $user->updated_at->format('d M, Y \a\t H:i') }}
                    </small>
                </div>
            </div>

            {{-- Danger Zone --}}
            @if(auth()->user()->role === 'admin')
                <div class="card shadow-sm border-0 rounded-3 mt-3">
                    <div class="card-header bg-danger text-white rounded-top-3">
                        <h5 class="mb-0">
                            <i class="fas fa-trash me-2"></i> Danger Zone
                        </h5>
                    </div>
                    <div class="card-body">
                        @if(auth()->user()->id !== $user->id)
                            <p class="text-muted mb-3">Deleting this user is permanent and cannot be undone.</p>
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger"
                                    onclick="return confirm('Are you sure you want to permanently delete this user? This action cannot be undone.')">
                                    <i class="fas fa-trash me-2"></i> Delete User
                                </button>
                            </form>
                        @else
                            <p class="text-muted mb-0">
                                <i class="fas fa-info-circle me-2"></i> You cannot delete your own account.
                            </p>
                        @endif
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
