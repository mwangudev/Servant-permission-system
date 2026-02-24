@extends('admin.layouts.app')

@section('title', 'View Leave Request')
@section('page-title', 'Leave Details')

@section('css')
<style>
    .signature-image {
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 10px;
        max-width: 300px;
        background-color: #f9f9f9;
    }

    #signature-pad {
        width: 100%;
        height: 200px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background: #fff;
    }

    .file-viewer-modal .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }

    .file-viewer-modal img,
    .file-viewer-modal iframe {
        max-width: 100%;
        height: auto;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Back Button --}}
    <div class="mb-3">
        @if(auth()->user()->role === 'employee')
            <a href="{{ route('leaves.showmy') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to My Leaves
            </a>
        @else
            <a href="{{ route('leaves.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to All Leaves
            </a>
        @endif
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Leave Request Details</h5>

            @if($leaveRequest->admin_signature && auth()->id() === $leaveRequest->user_id)
                <a href="{{ route('leaves.downloadPDF', $leaveRequest->id) }}"
                   class="btn btn-success btn-sm">
                    <i class="fas fa-download me-1"></i> Download PDF
                </a>
            @endif
        </div>

        <div class="card-body">
            <div class="row">

                {{-- LEFT SIDE --}}
                <div class="col-md-8">

                    <div class="mb-3">
                        <strong>Leave Type:</strong>
                        {{ ucfirst($leaveRequest->request_type ?? '—') }}
                    </div>

                    <div class="mb-3">
                        <strong>Duration:</strong>
                        @if($leaveRequest->start_date && $leaveRequest->end_date)
                            @php
                                $start = \Carbon\Carbon::parse($leaveRequest->start_date);
                                $end = \Carbon\Carbon::parse($leaveRequest->end_date);
                            @endphp
                            {{ $start->format('d M Y') }} -
                            {{ $end->format('d M Y') }}
                            ({{ $start->diffInDays($end) + 1 }} days)
                        @else
                            —
                        @endif
                    </div>

                    @php
                        $statuses = [
                            'submitted' => ['class' => 'bg-info', 'icon' => 'fa-paper-plane'],
                            'pending' => ['class' => 'bg-warning', 'icon' => 'fa-clock'],
                            'on_progress' => ['class' => 'bg-primary', 'icon' => 'fa-spinner'],
                            'approved' => ['class' => 'bg-success', 'icon' => 'fa-check'],
                            'rejected' => ['class' => 'bg-danger', 'icon' => 'fa-times'],
                        ];
                        $statusData = $statuses[$leaveRequest->status]
                                      ?? ['class' => 'bg-secondary', 'icon' => 'fa-question'];
                    @endphp

                    <div class="mb-3">
                        <strong>Status:</strong>
                        <span class="badge {{ $statusData['class'] }} px-3 py-2">
                            <i class="fas {{ $statusData['icon'] }} me-1"></i>
                            {{ strtoupper(str_replace('_', ' ', $leaveRequest->status ?? 'UNKNOWN')) }}
                        </span>
                    </div>

                    {{-- Approve / Reject --}}
                    @if(
                        (auth()->user()->role === 'hod'
                            && in_array($leaveRequest->status, ['submitted','pending','on_progress']))
                        ||
                        (auth()->user()->role === 'admin'
                            && $leaveRequest->status === 'pending')
                    )
                        <div class="mt-4">
                            <button class="btn btn-success me-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#approveModal">
                                Approve
                            </button>

                            <button class="btn btn-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#rejectModal">
                                Reject
                            </button>
                        </div>
                    @endif

                    {{-- Signatures --}}
                    @if($leaveRequest->hod_signature)
                        <div class="mt-5">
                            <strong>HOD Signature:</strong>
                            <div class="mt-2">
                                <img src="{{ $leaveRequest->hod_signature }}"
                                     class="signature-image img-fluid">
                            </div>
                        </div>
                    @endif

                    @if($leaveRequest->admin_signature)
                        <div class="mt-4">
                            <strong>Admin Signature:</strong>
                            <div class="mt-2">
                                <img src="{{ $leaveRequest->admin_signature }}"
                                     class="signature-image img-fluid">
                            </div>
                        </div>
                    @endif

                </div>

                {{-- RIGHT SIDE (Timeline unchanged) --}}
                <div class="col-md-4">
                    <strong>Leave Progress Timeline:</strong>
                    <ul class="list-unstyled mt-3">
                        @foreach($leaveRequest->histories()->orderBy('created_at')->get() as $history)
                            <li class="mb-3">
                                <span class="badge bg-secondary me-2">
                                    {{ \Carbon\Carbon::parse($history->created_at)->format('d M Y H:i') }}
                                </span>
                                <span class="fw-bold text-capitalize">
                                    {{ str_replace('_', ' ', $history->action) }}
                                </span>
                                @if($history->user)
                                    <div class="text-muted small">
                                        by {{ $history->user->full_name }}
                                    </div>
                                @endif
                                @if($history->remarks)
                                    <div class="text-muted small">
                                        Remarks: {{ $history->remarks }}
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>

            </div>
        </div>
    </div>
</div>


{{-- APPROVE MODAL (Digital Signature) --}}
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('leaves.approve', $leaveRequest->id) }}"
                  method="POST">
                @method('PUT')
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Approve Leave Request</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <label class="form-label">Digital Signature</label>

                    <canvas id="signature-pad"></canvas>
                    <input type="hidden" name="digital_signature" id="digital_signature">

                    <button type="button"
                            id="clear-signature"
                            class="btn btn-sm btn-secondary mt-2">
                        Clear
                    </button>

                    <label class="form-label mt-3">Remarks (Optional)</label>
                    <textarea name="remarks"
                              class="form-control"
                              rows="3"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button>

                    <button type="submit"
                            class="btn btn-success">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection


@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
let signaturePad;

$('#approveModal').on('shown.bs.modal', function () {
    const canvas = document.getElementById('signature-pad');

    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext("2d").scale(ratio, ratio);

    signaturePad = new SignaturePad(canvas);
});

document.getElementById('clear-signature')
    .addEventListener('click', function () {
        signaturePad.clear();
    });

document.querySelector('#approveModal form')
    .addEventListener('submit', function (e) {
        if (signaturePad.isEmpty()) {
            e.preventDefault();
            alert('Please provide a signature.');
            return;
        }
        document.getElementById('digital_signature').value =
            signaturePad.toDataURL('image/png');
    });
</script>
@endsection
