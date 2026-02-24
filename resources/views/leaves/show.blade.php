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

    .file-viewer-modal .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }

    .file-viewer-modal img,
    .file-viewer-modal iframe {
        max-width: 100%;
        height: auto;
    }

    #signature-pad {
        border: 1px solid #ced4da;
        border-radius: 5px;
        width: 100%;
        height: 200px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">

    {{-- Back Button --}}
    <div class="mb-3">
        @if(Auth::user()->role === 'employee')
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

            @if($leaveRequest->admin_signature && auth()->user()->id === $leaveRequest->user_id)
                <a href="{{ route('leaves.downloadPDF', $leaveRequest->id) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-download me-1"></i> Download PDF
                </a>
            @endif
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-12">

                    <div class="mb-3">
                        <strong>Leave Type:</strong> {{ ucfirst($leaveRequest->request_type) }}
                    </div>

                    <div class="mb-3">
                        <strong>Duration:</strong>
                        {{ $leaveRequest->start_date ? \Carbon\Carbon::parse($leaveRequest->start_date)->format('d M Y') : '—' }}
                        -
                        {{ $leaveRequest->end_date ? \Carbon\Carbon::parse($leaveRequest->end_date)->format('d M Y') : '—' }}
                        ({{ \Carbon\Carbon::parse($leaveRequest->start_date)->diffInDays(\Carbon\Carbon::parse($leaveRequest->end_date)) + 1 }} days)
                    </div>

                    {{-- Status Badge --}}
                    @php
                        $statuses = [
                            'submitted' => ['class' => 'bg-info', 'icon' => 'fa-paper-plane'],
                            'pending' => ['class' => 'bg-warning', 'icon' => 'fa-clock'],
                            'on_progress' => ['class' => 'bg-primary', 'icon' => 'fa-spinner'],
                            'approved' => ['class' => 'bg-success', 'icon' => 'fa-check'],
                            'rejected' => ['class' => 'bg-danger', 'icon' => 'fa-times'],
                        ];
                        $statusData = $statuses[$leaveRequest->status] ?? ['class' => 'bg-secondary', 'icon' => 'fa-question'];
                    @endphp

                    <div class="mb-3">
                        <strong>Status:</strong>
                        <span class="badge {{ $statusData['class'] }} px-3 py-2">
                            <i class="fas {{ $statusData['icon'] }} me-1"></i>
                            {{ strtoupper(str_replace('_', ' ', $leaveRequest->status)) }}
                        </span>
                    </div>

                    {{-- Report --}}
                    @if($leaveRequest->report_path)
                        <div class="mb-3">
                            <strong>Attached Report:</strong>
                            <div class="d-flex gap-2 mt-1">
                                <button type="button" class="btn btn-outline-primary btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#fileViewerModal">
                                    <i class="fas fa-eye me-1"></i> View
                                </button>
                                <a href="{{ asset('storage/' . $leaveRequest->report_path) }}"
                                   class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-download me-1"></i> Download
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- Approve / Reject Buttons --}}
                    @if(in_array(auth()->user()->role, ['hod', 'admin'])
                        && in_array($leaveRequest->status, ['submitted', 'pending', 'on_progress']))
                        <div class="mt-4">
                            <button type="button" class="btn btn-success me-2"
                                    data-bs-toggle="modal" data-bs-target="#approveModal">
                                <i class="fas fa-check me-1"></i> Approve
                            </button>
                            <button type="button" class="btn btn-danger"
                                    data-bs-toggle="modal" data-bs-target="#rejectModal">
                                <i class="fas fa-times me-1"></i> Reject
                            </button>
                        </div>
                    @endif

                    {{-- Signatures --}}
                    @if($leaveRequest->hod_signature)
                        <div class="mt-5">
                            <strong>HOD Signature:</strong>
                            <div class="mt-2">
                                <img src="{{ $leaveRequest->hod_signature }}"
                                     class="signature-image img-fluid"
                                     alt="HOD Signature">
                            </div>
                        </div>
                    @endif

                    @if($leaveRequest->admin_signature)
                        <div class="mt-4">
                            <strong>Admin Signature:</strong>
                            <div class="mt-2">
                                <img src="{{ $leaveRequest->admin_signature }}"
                                     class="signature-image img-fluid"
                                     alt="Admin Signature">
                            </div>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- File Viewer Modal --}}
@if($leaveRequest->report_path)
<div class="modal fade file-viewer-modal" id="fileViewerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">File Viewer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @php
                    $filePath = $leaveRequest->report_path;
                    $fileExt = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                @endphp

                @if(in_array($fileExt, ['jpg','jpeg','png','gif']))
                    <img src="{{ asset('storage/' . $filePath) }}" class="img-fluid">
                @elseif($fileExt === 'pdf')
                    <iframe src="{{ asset('storage/' . $filePath) }}" width="100%" height="600px"></iframe>
                @else
                    <div class="alert alert-info">
                        File format not supported.
                        <a href="{{ asset('storage/' . $filePath) }}"
                           target="_blank"
                           class="btn btn-sm btn-primary ms-2">
                            Download
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

{{-- Approve Modal --}}
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('leaves.approve', $leaveRequest->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Approve Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Draw Signature</label>
                    <canvas id="signature-pad"></canvas>
                    <input type="hidden" name="digital_signature" id="digital_signature">

                    <div class="mt-3">
                        <button type="button" class="btn btn-sm btn-secondary"
                                id="clear-signature">Clear</button>
                    </div>

                    <div class="mb-3 mt-3">
                        <label for="hod_remarks" class="form-label">Remarks (optional)</label>
                        <textarea name="hod_remarks"
                                  id="hod_remarks"
                                  class="form-control"
                                  rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                    <button type="submit"
                            class="btn btn-success"
                            id="submit-approval">Approve & Set Pending</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('leaves.reject', $leaveRequest->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks"
                              class="form-control"
                              rows="3"
                              required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                    <button type="submit"
                            class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('js')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    (function() {
        let signaturePad = null;

        function resizeCanvas(canvas) {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const width = canvas.offsetWidth;
            const height = canvas.offsetHeight || 200;
            canvas.width = Math.floor(width * ratio);
            canvas.height = Math.floor(height * ratio);
            const ctx = canvas.getContext('2d');
            ctx.scale(ratio, ratio);
        }

        function initSignaturePad() {
            const canvas = document.getElementById('signature-pad');
            if (!canvas) return;
            // Ensure canvas visible size
            canvas.style.width = '100%';
            canvas.style.height = '200px';
            resizeCanvas(canvas);
            signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgba(255,255,255,0)',
                penColor: 'rgb(0,0,0)'
            });
        }

        const approveModalEl = document.getElementById('approveModal');
        if (approveModalEl) {
            approveModalEl.addEventListener('shown.bs.modal', function () {
                // init after modal is visible to get proper sizes
                initSignaturePad();
            });

            approveModalEl.addEventListener('hidden.bs.modal', function () {
                if (signaturePad) signaturePad.clear();
            });
        }

        const clearBtn = document.getElementById('clear-signature');
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                if (signaturePad) signaturePad.clear();
            });
        }

        // On form submit, validate and write data URL to hidden input
        const approveForm = document.querySelector('#approveModal form');
        if (approveForm) {
            approveForm.addEventListener('submit', function (e) {
                const hiddenInput = document.getElementById('digital_signature');
                if (!signaturePad || signaturePad.isEmpty()) {
                    e.preventDefault();
                    alert('Please provide a signature before submitting.');
                    return;
                }
                // set PNG data URL
                hiddenInput.value = signaturePad.toDataURL('image/png');
            });
        }

        // Preserve strokes on resize
        window.addEventListener('resize', function () {
            const canvas = document.getElementById('signature-pad');
            if (!canvas || !signaturePad) return;
            const data = signaturePad.toData();
            resizeCanvas(canvas);
            signaturePad.clear();
            signaturePad.fromData(data);
        });
    })();
</script>
@endsection
