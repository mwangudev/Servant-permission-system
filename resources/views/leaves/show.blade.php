@extends('admin.layouts.app')

@section('content')
<div class="container">

@php
    $user = $leaveRequest->user;
    $department = $user?->department;

    $days = ($leaveRequest->start_date && $leaveRequest->end_date)
        ? \Carbon\Carbon::parse($leaveRequest->start_date)
            ->diffInDays(\Carbon\Carbon::parse($leaveRequest->end_date)) + 1
        : null;

    $statusColors = [
        'submitted' => 'secondary',
        'pending'   => 'warning',
        'approved'  => 'success',
        'rejected'  => 'danger',
    ];

    $canApproveOrReject = false;
    if (auth()->user()->role === 'hod' && $leaveRequest->status === 'submitted') {
        $canApproveOrReject = true;
    }
    if (auth()->user()->role === 'admin' && $leaveRequest->status === 'pending') {
        $canApproveOrReject = true;
    }
@endphp


<style>
.timeline {
    position: relative;
    padding-left: 40px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 3px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 35px;
}

.timeline-dot {
    position: absolute;
    left: -27px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 0 0 3px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 12px 15px;
    border-radius: 8px;
}
</style>


<div class="card shadow-lg border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold">Leave Request</h5>
            <small class="text-muted">
                Submitted {{ $leaveRequest->created_at?->diffForHumans() }}
            </small>
        </div>

        <span class="badge bg-{{ $statusColors[$leaveRequest->status] ?? 'secondary' }} px-3 py-2">
            {{ strtoupper($leaveRequest->status) }}
        </span>
    </div>

    <div class="card-body">
        <div class="row">

            {{-- LEFT SIDE : DETAILS --}}
            <div class="col-md-6 border-end pe-4">

                <h6 class="text-primary fw-bold">Employee Information</h6>
                <hr>

                <p><strong>Full Name:</strong>
                    {{ $user ? trim($user->fname.' '.$user->mname.' '.$user->lname) : 'N/A' }}
                </p>

                <p><strong>Department:</strong>
                    {{ $department?->name ?? 'N/A' }}
                </p>

                <p><strong>Position:</strong>
                    {{ $user?->assigned_as ?? 'N/A' }}
                </p>

                <p><strong>Request Type:</strong>
                    {{ $leaveRequest->request_type ?? 'N/A' }}
                </p>

                <h6 class="text-primary fw-bold mt-4">Leave Details</h6>
                <hr>

                <p><strong>Start Date:</strong>
                    {{ $leaveRequest->start_date?->format('d M Y') }}
                </p>

                <p><strong>End Date:</strong>
                    {{ $leaveRequest->end_date?->format('d M Y') }}
                </p>

                <p><strong>Total Days:</strong>
                    {{ $days ?? 'N/A' }}
                </p>

                <p><strong>Reason:</strong><br>
                    {{ $leaveRequest->reasons ?? 'N/A' }}
                </p>

            </div>


            {{-- RIGHT SIDE : CONNECTED TIMELINE --}}
            <div class="col-md-6 ps-4">

                <h6 class="text-primary fw-bold">Progress Timeline</h6>
                <hr>

                <div class="timeline">

                    {{-- Submitted --}}
                    <div class="timeline-item">
                        <div class="timeline-dot bg-info"></div>
                        <div class="timeline-content">
                            <strong>Submitted</strong>
                            <div class="small text-muted">
                                {{ $leaveRequest->created_at?->format('d M Y H:i') }}
                                ({{ $leaveRequest->created_at?->diffForHumans() }})
                            </div>
                        </div>
                    </div>

                    {{-- HOD --}}
                    @if($leaveRequest->hod_signed_at)
                        <div class="timeline-item">
                            <div class="timeline-dot bg-warning"></div>
                            <div class="timeline-content">
                                <strong>HOD Reviewed</strong>
                                <div class="small text-muted">
                                    {{ $leaveRequest->hod_signed_at?->format('d M Y H:i') }}
                                    ({{ $leaveRequest->hod_signed_at?->diffForHumans() }})
                                </div>

                                @if($leaveRequest->hod_remarks)
                                    <div class="small mt-2">
                                        <strong>Remarks:</strong> {{ $leaveRequest->hod_remarks }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Admin --}}
                    @if($leaveRequest->admin_signed_at)
                        <div class="timeline-item">
                            <div class="timeline-dot bg-success"></div>
                            <div class="timeline-content">
                                <strong>Admin Decision</strong>
                                <div class="small text-muted">
                                    {{ $leaveRequest->admin_signed_at?->format('d M Y H:i') }}
                                    ({{ $leaveRequest->admin_signed_at?->diffForHumans() }})
                                </div>

                                @if($leaveRequest->admin_remarks)
                                    <div class="small mt-2">
                                        <strong>Remarks:</strong> {{ $leaveRequest->admin_remarks }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                </div>

            </div>
        </div>
    </div>


    <div class="card-footer bg-white text-end">

        @if($canApproveOrReject)
            <button class="btn btn-success btn-sm me-2"
                    data-bs-toggle="modal"
                    data-bs-target="#approveModal">
                Approve
            </button>

            <button class="btn btn-danger btn-sm me-2"
                    data-bs-toggle="modal"
                    data-bs-target="#rejectModal">
                Reject
            </button>
        @endif

        <a href="{{ route('leaves.index') }}"
           class="btn btn-secondary btn-sm">
            Back
        </a>
    </div>
</div>

</div>
@endsection
