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

    $histories = $leaveRequest->histories()->with('user')->orderBy('created_at')->get();
@endphp

<style>
.timeline {
    position: relative;
    padding-left: 40px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 18px;
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
    left: -29px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 4px solid #fff;
}
.timeline-content {
    background: #f8f9fa;
    padding: 14px 18px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.05);
}
</style>

<div class="card shadow-lg border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-0">Leave Request</h5>
            <small class="text-muted">
                Submitted {{ $leaveRequest->created_at?->diffForHumans() }}
            </small>
        </div>

        <span class="badge bg-{{ $statusColors[$leaveRequest->status] ?? 'secondary' }} px-3 py-2">
            {{ strtoupper($leaveRequest->status) }}
        </span>

        @if($leaveRequest->status === 'approved')
        <a href="{{ route('leaves.download', $leaveRequest->id) }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-download"></i> Download PDF
        </a>
        @endif
    </div>

    <div class="card-body">
        <div class="row">

            {{-- LEFT SIDE --}}
            <div class="col-md-6 border-end pe-4">
                <h6 class="text-primary fw-bold">Employee Information</h6>
                <hr>
                <p><strong>Full Name:</strong> {{ $user ? trim($user->fname.' '.$user->mname.' '.$user->lname) : 'N/A' }}</p>
                <p><strong>Department:</strong> {{ $department?->name ?? 'N/A' }}</p>
                <p><strong>Position:</strong> {{ $user?->assigned_as ?? 'N/A' }}</p>
                <p><strong>Request Type:</strong> {{ $leaveRequest->request_type ?? 'N/A' }}</p>

                <h6 class="text-primary fw-bold mt-4">Leave Details</h6>
                <hr>
                <p><strong>Start Date:</strong> {{ $leaveRequest->start_date?->format('d M Y') }}</p>
                <p><strong>End Date:</strong> {{ $leaveRequest->end_date?->format('d M Y') }}</p>
                <p><strong>Total Days:</strong> {{ $days ?? 'N/A' }}</p>
                <p><strong>Reason:</strong><br>{{ $leaveRequest->reasons ?? 'N/A' }}</p>
                <p><strong>Destination:</strong><br>{{ $leaveRequest->destination ?? 'N/A' }}</p>
            </div>

            {{-- RIGHT SIDE TIMELINE --}}
            <div class="col-md-6 ps-4">
                <h6 class="text-primary fw-bold">Progress Timeline</h6>
                <hr>
                <div class="timeline">
                    @foreach($histories as $history)
                        <div class="timeline-item">
                            <div class="timeline-dot
                                @if(str_contains($history->action, 'approved')) bg-success
                                @elseif(str_contains($history->action, 'rejected')) bg-danger
                                @else bg-info
                                @endif">
                            </div>
                            <div class="timeline-content">
                                <strong>{{ ucwords(str_replace('_',' ', $history->action)) }}</strong>
                                <div class="small text-muted">
                                    {{ $history->created_at->format('d M Y H:i') }}
                                    ({{ $history->created_at->diffForHumans() }})
                                    @if($history->user)
                                        - by {{ $history->user->fname ?? '' }} {{ $history->user->lname ?? '' }}
                                    @endif
                                </div>
                                @if($history->remarks)
                                <div class="small mt-2">{{ $history->remarks }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="card-footer text-end bg-white">
        @if($canApproveOrReject)
            <button class="btn btn-success btn-sm me-2" data-bs-toggle="modal" data-bs-target="#approveModal">Approve</button>
            <button class="btn btn-danger btn-sm me-2" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>
        @endif
        <a href="{{ route('leaves.index') }}" class="btn btn-secondary btn-sm">Back</a>
    </div>
</div>

{{-- APPROVE MODAL --}}
<div class="modal fade" id="approveModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<form method="POST" action="{{ route('leaves.approve', $leaveRequest->id) }}">
@csrf @method('PATCH')
<input type="hidden" name="signature" id="approve_signature">

<div class="modal-content">
<div class="modal-header bg-success text-white">
<h5 class="modal-title">Approve Leave</h5>
<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<textarea name="{{ auth()->user()->role === 'hod' ? 'hod_remarks' : 'admin_remarks' }}"
class="form-control mb-3" placeholder="Remarks (optional)"></textarea>

<canvas id="approveCanvas" style="width:100%;height:200px;border:1px solid #ccc;border-radius:6px;"></canvas>
<button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="approvePad.clear()">Clear</button>
</div>

<div class="modal-footer">
<button type="submit" class="btn btn-success btn-sm" onclick="saveApprove(event)">Confirm</button>
</div>
</div>
</form>
</div>
</div>

{{-- REJECT MODAL --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<form method="POST" action="{{ route('leaves.reject', $leaveRequest->id) }}">
@csrf @method('PATCH')
<input type="hidden" name="signature" id="reject_signature">

<div class="modal-content">
<div class="modal-header bg-danger text-white">
<h5 class="modal-title">Reject Leave</h5>
<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<textarea required name="{{ auth()->user()->role === 'hod' ? 'hod_remarks' : 'admin_remarks' }}"
class="form-control mb-3" placeholder="Rejection reason"></textarea>

<canvas id="rejectCanvas" style="width:100%;height:200px;border:1px solid #ccc;border-radius:6px;"></canvas>
<button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="rejectPad.clear()">Clear</button>
</div>

<div class="modal-footer">
<button type="submit" class="btn btn-danger btn-sm" onclick="saveReject(event)">Confirm</button>
</div>
</div>
</form>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
let approvePad, rejectPad;

document.addEventListener("DOMContentLoaded", function () {
    approvePad = new SignaturePad(document.getElementById('approveCanvas'));
    rejectPad = new SignaturePad(document.getElementById('rejectCanvas'));
});

function saveApprove(e){
    if(approvePad.isEmpty()){
        alert("Signature required.");
        e.preventDefault();
        return;
    }
    document.getElementById('approve_signature').value = approvePad.toDataURL('image/png');
}

function saveReject(e){
    if(rejectPad.isEmpty()){
        alert("Signature required.");
        e.preventDefault();
        return;
    }
    document.getElementById('reject_signature').value = rejectPad.toDataURL('image/png');
}
</script>

@endsection
