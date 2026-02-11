@extends('admin.layouts.app')

@section('title', 'View Leave Request')
@section('page-title', 'Leave Details')

@section('content')
<div class="container-fluid">

    {{-- Back Button --}}
    <div class="mb-3">
        <a href="{{ route('leaves.showmy') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Back to My Leaves
        </a>
    </div>

    {{-- Leave Details Card --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light">
            <h5 class="mb-0">Leave Request Details</h5>
        </div>

        <div class="card-body">

            {{-- Leave Type --}}
            <div class="row mb-3">
                <div class="col-md-4 fw-semibold">Leave Type:</div>
                <div class="col-md-8">{{ ucfirst($leaveRequest->request_type) }}</div>
            </div>

            {{-- Duration --}}
            <div class="row mb-3">
                <div class="col-md-4 fw-semibold">Duration:</div>
                <div class="col-md-8">
                    {{ $leaveRequest->start_date ? \Carbon\Carbon::parse($leaveRequest->start_date)->format('d M Y') : '—' }}
                    -
                    {{ $leaveRequest->end_date ? \Carbon\Carbon::parse($leaveRequest->end_date)->format('d M Y') : '—' }}
                </div>
            </div>

            {{-- Status --}}
            <div class="row mb-3">
                <div class="col-md-4 fw-semibold">Status:</div>
                <div class="col-md-8">
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

                    <span class="badge {{ $statusData['class'] }} px-3 py-2">
                        <i class="fas {{ $statusData['icon'] }} me-1"></i>
                        {{ strtoupper(str_replace('_', ' ', $leaveRequest->status)) }}
                    </span>
                </div>
            </div>

            {{-- Report File --}}
            @if($leaveRequest->report_path)
            <div class="row mb-3">
                <div class="col-md-4 fw-semibold">Attached Report:</div>
                <div class="col-md-8">
                    <a href="{{ asset('storage/' . $leaveRequest->report_path) }}" 
                       target="_blank" 
                       class="btn btn-outline-info btn-sm">
                        <i class="fas fa-download me-1"></i> Download File
                    </a>
                </div>
            </div>
            @endif

            {{-- Admin Remark --}}
            @if($leaveRequest->admin_remark)
            <div class="row mb-3">
                <div class="col-md-4 fw-semibold">Admin Remark:</div>
                <div class="col-md-8">
                    <span class="text-muted">{{ $leaveRequest->admin_remark }}</span>
                </div>
            </div>
            @endif

            {{-- Created & Updated --}}
            <div class="row mb-3">
                <div class="col-md-4 fw-semibold">Submitted At:</div>
                <div class="col-md-8">
                    {{ $leaveRequest->created_at?->format('d M Y H:i') ?? '—' }}
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4 fw-semibold">Last Updated:</div>
                <div class="col-md-8">
                    {{ $leaveRequest->updated_at?->format('d M Y H:i') ?? '—' }}
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
