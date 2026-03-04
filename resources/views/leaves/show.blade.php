@extends('admin.layouts.app')

@section('title','Leave Request Details')

@section('content')
<div class="container py-4">

@php
    $user = $leaveRequest->user;
    $department = $user?->department;

    $days = ($leaveRequest->start_date && $leaveRequest->end_date)
        ? \Carbon\Carbon::parse($leaveRequest->start_date)
            ->diffInDays(\Carbon\Carbon::parse($leaveRequest->end_date)) + 1
        : null;

    $status = $leaveRequest->status;

    // Progress percentage
    $progress = match($status) {
        'submitted' => 25,
        'pending'   => 50,
        'approved'  => 100,
        'rejected'  => 100,
        default     => 10
    };

    $canApproveOrReject = false;
    if (auth()->user()->role === 'hod' && $status === 'submitted') {
        $canApproveOrReject = true;
    }
    if (auth()->user()->role === 'admin' && $status === 'pending') {
        $canApproveOrReject = true;
    }

    $histories = $leaveRequest->leavehistories()
                    ->with('user')
                    ->orderBy('created_at')
                    ->get();
@endphp


<div class="card shadow border-0">

    {{-- HEADER --}}
<div class="card-header bg-white">
    <div class="d-flex justify-content-between align-items-center">

        <div>
            <h5 class="fw-bold mb-0">Leave Request</h5>
            <small class="text-muted">
                Submitted {{ $leaveRequest->created_at?->format('d M Y H:i') }}
                ({{ $leaveRequest->created_at?->diffForHumans() }})
            </small>
        </div>

        <div class="d-flex align-items-center gap-2">

            {{-- STATUS BADGE --}}
            <span class="badge
                @if($status=='approved') bg-success
                @elseif($status=='rejected') bg-danger
                @elseif($status=='pending') bg-warning
                @else bg-secondary
                @endif px-3 py-2">
                {{ strtoupper($status) }}
            </span>

            {{-- DOWNLOAD PDF --}}
            @if($status === 'approved')
                <a href="{{ route('leaves.download', $leaveRequest->id) }}"
                   class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-file-pdf me-1"></i> Download PDF
                </a>
            @endif

        </div>

    </div>

    {{-- PROGRESS BAR --}}
    <div class="mt-3">
        <div class="progress" style="height:8px;">
            <div class="progress-bar
                @if($status=='approved') bg-success
                @elseif($status=='rejected') bg-danger
                @else bg-primary
                @endif"
                style="width: {{ $progress }}%">
            </div>
        </div>

        <div class="d-flex justify-content-between small mt-2 text-muted">
            <span>Submitted</span>
            <span>HOD Review</span>
            <span>Admin Approval</span>
            <span>Completed</span>
        </div>
    </div>
</div>


    {{-- BODY --}}
    <div class="card-body">
        <div class="row">

            {{-- LEFT SIDE --}}
            <div class="col-md-6 border-end">

                <h6 class="fw-bold text-primary">Employee Information</h6>
                <hr>

                <p><strong>Full Name:</strong>
                    {{ $user ? trim($user->fname.' '.$user->mname.' '.$user->lname) : 'N/A' }}
                </p>

                <p><strong>Department:</strong> {{ $department?->name ?? 'N/A' }}</p>
                <p><strong>Position:</strong> {{ $user?->assigned_as ?? 'N/A' }}</p>
                <p><strong>Request Type:</strong> {{ ucfirst($leaveRequest->request_type) }}</p>

                <h6 class="fw-bold text-primary mt-4">Leave Details</h6>
                <hr>

                <p><strong>Start Date:</strong> {{ $leaveRequest->start_date?->format('d M Y') }}</p>
                <p><strong>End Date:</strong> {{ $leaveRequest->end_date?->format('d M Y') }}</p>
                <p><strong>Total Days:</strong> {{ $days }}</p>
                <p><strong>Reason:</strong><br>{{ $leaveRequest->reasons }}</p>
                <p><strong>Destination:</strong><br>{{ $leaveRequest->destination }}</p>

            </div>


            {{-- RIGHT SIDE TIMELINE --}}
            <div class="col-md-6">

                <h6 class="fw-bold text-primary">Approval Timeline</h6>
                <hr>

                <div class="timeline-modern">

                    @foreach($histories as $history)

                        @php
                            $color = 'primary';
                            if(str_contains($history->action,'approved')) $color='success';
                            if(str_contains($history->action,'rejected')) $color='danger';
                        @endphp

                        <div class="timeline-modern-item">
                            <div class="timeline-icon bg-{{ $color }}">
                                <i class="fas fa-check text-white"></i>
                            </div>

                            <div class="timeline-card shadow-sm">
                                <div class="fw-bold">
                                    {{ ucwords(str_replace('_',' ', $history->action)) }}
                                </div>

                                <small class="text-muted">
                                    {{ $history->created_at?->format('d M Y H:i') }}
                                    ({{ $history->created_at?->diffForHumans() }})
                                    @if($history->user)
                                        - by {{ $history->user->fname }} {{ $history->user->lname }}
                                        ({{ strtoupper($history->user->role) }})
                                    @endif
                                </small>

                                @if($history->remarks)
                                    <div class="mt-2">
                                        <strong>Remarks:</strong>
                                        <div class="text-muted">{{ $history->remarks }}</div>
                                    </div>
                                @endif

                            </div>
                        </div>

                    @endforeach

                </div>

            </div>
        </div>
    </div>


    {{-- FOOTER --}}
    <div class="card-footer text-end bg-white">

        @if($canApproveOrReject)
            <button class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#approveModal">
                Approve
            </button>

            <button class="btn btn-danger btn-sm me-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
                Reject
            </button>
        @endif

        <a href="{{ route('leaves.index') }}" class="btn btn-secondary btn-sm">
            Back
        </a>
    </div>

</div>
</div>


{{-- MODERN TIMELINE STYLE --}}
@push('css')
<style>

.timeline-modern {
    position: relative;
    padding-left: 40px;
}

.timeline-modern::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    width: 3px;
    height: 100%;
    background: #dee2e6;
}

.timeline-modern-item {
    position: relative;
    margin-bottom: 30px;
}

.timeline-icon {
    position: absolute;
    left: -25px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display:flex;
    align-items:center;
    justify-content:center;
}

.timeline-card {
    background: #fff;
    padding: 15px;
    border-radius: 10px;
    border-left: 4px solid #0d6efd;
}

</style>
@endpush

@endsection
