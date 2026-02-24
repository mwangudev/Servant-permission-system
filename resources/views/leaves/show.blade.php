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

    /* Nimetoa CSS ya zamani ya #signature-pad hapa kwani sasa tunatumia Wrapper */
</style>
@endsection

@section('content')
<div class="container-fluid">


    {{-- KODI YA KUNASA NA KUONYESHA ERRORS NA SUCCESS MESSAGES --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mt-1" role="alert">
            <strong>Errors:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <i class="fas fa-exclamation-triangle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    {{-- MWISHO WA KODI YA ERRORS --}}

    {{-- Back Button yako inaendelea hapa chini... --}}

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
                    <label class="form-label fw-bold text-primary">Draw Signature Here</label>

                    {{-- WRAPPER MPYA YENYE VIPIMO SAHIHI --}}
                    <div style="position: relative; width: 100%; height: 200px; border: 2px dashed #0d6efd; background-color: #f8f9fa; border-radius: 8px; touch-action: none; overflow: hidden;">
                        <canvas id="signature-pad" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: crosshair;"></canvas>
                    </div>
                    <input type="hidden" name="digital_signature" id="digital_signature">

                    <div class="mt-3">
                        <button type="button" class="btn btn-sm btn-danger" id="clear-signature">
                            <i class="fas fa-eraser me-1"></i> Clear Signature
                        </button>
                    </div>

                    <div class="mb-3 mt-4">
                        <label for="hod_remarks" class="form-label fw-bold">Remarks (optional)</label>
                        <textarea name="remarks"
                                  id="remarks"
                                  class="form-control"
                                  rows="3" placeholder="Write any comments here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                    <button type="submit"
                            class="btn btn-success"
                            id="submit-approval">Approve</button>
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
                       public function approve(Request $request, $id)
    {
        $Authuser = auth()->user();


            $request->validate([
                'digital_signature' => 'required|string',
                'remarks' => 'nullable|string',

            ]);




        // 2. Tafuta hiyo likizo kwenye database
        $leaveRequest = LeaveRequest::findOrFail($id);

        // 3. Pata picha ya saini kutoka kwenye Base64 Text
        $signatureData = $request->input('digital_signature');
        $image_parts = explode(";base64,", $signatureData);
        $image_base64 = base64_decode($image_parts[1]);

        $leaveRequest->status = 'approved';

       // 3. HIFADHI PICHA (Kama kawaida)
    $leaveRequest = LeaveRequest::findOrFail($id);
    $fileName = 'signature_' . $Authuser . '_' . $leaveRequest->id . '_' . time() . '.png';
    $filePath = 'signatures/leaves/' . $fileName;
    Storage::disk('public')->put($filePath, $image_base64);

    // 4. WEKA KWENYE DATABASE KULINGANA NA CHEO
    if ($Authuser === 'hod') {
        $leaveRequest->hod_signature = 'storage/' . $filePath;
        $leaveRequest->hod_remarks = $request->remarks;
        $leaveRequest->status = 'pending'; // Inasubiri Admin
    } elseif ($Authuser === 'admin') {
        $leaveRequest->admin_signature = 'storage/' . $filePath;
        $leaveRequest->admin_remarks = $request->remarks; // Kama unayo DB
        $leaveRequest->status = 'approved'; // Imekamilika
    }

        $leaveRequest->save();

        // 7. Rudisha ujumbe wa pongezi
        return redirect()->back()->with('success', 'Leave request imepitishwa na kusainiwa kikamilifu!');
    }     class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var canvas = document.getElementById('signature-pad');
        var signaturePad;
        var approveModalEl = document.getElementById('approveModal');

        if (approveModalEl) {
            approveModalEl.addEventListener('shown.bs.modal', function () {
                // TUNAWEKA DELAY YA SEKUNDE 0.2 ILI MODAL ITULIE KWANZA
                setTimeout(function() {
                    var ratio = Math.max(window.devicePixelRatio || 1, 1);
                    var wrapper = canvas.parentElement; // Tunachukua ukubwa wa kiboksi cha nje

                    canvas.width = wrapper.offsetWidth * ratio;
                    canvas.height = wrapper.offsetHeight * ratio;
                    canvas.getContext("2d").scale(ratio, ratio);

                    if (!signaturePad) {
                        signaturePad = new SignaturePad(canvas, {
                            backgroundColor: 'rgba(255, 255, 255, 0)', // Background wazi
                            penColor: 'rgb(0, 0, 0)', // Wino mweusi
                            minWidth: 1.5,
                            maxWidth: 3.0
                        });
                    } else {
                        signaturePad.clear(); // Futa kama kuna makosa ya awali
                    }
                }, 200); // Miliseconds 200 ndio uchawi wenyewe
            });

            // Futa (Clear) Button
            var clearBtn = document.getElementById('clear-signature');
            if (clearBtn) {
                clearBtn.addEventListener('click', function () {
                    if (signaturePad) signaturePad.clear();
                });
            }

            // Wakati wa kutuma fomu
            var approveForm = document.querySelector('#approveModal form');
            if (approveForm) {
                approveForm.addEventListener('submit', function (e) {
                    if (!signaturePad || signaturePad.isEmpty()) {
                        e.preventDefault();
                        alert('⚠️ Tafadhali weka saini yako kwanza.');
                    } else {
                        // Badilisha mchoro uwe picha na iweke kwenye input
                        document.getElementById('digital_signature').value = signaturePad.toDataURL('image/png');
                    }
                });
            }
        }
    });
</script>
@endsection

