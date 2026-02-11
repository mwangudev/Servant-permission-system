@extends('admin.layouts.app')

@section('title', 'All Leaves')
@section('page-title', 'All Leaves')

@section('content')
<div class="container-fluid">

    {{-- Top Actions --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Leave Requests Lists</h5>        
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Leaves Table --}}
    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">

            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Report</th>
                        <th width="220">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($myleaves as $leave)
                        @php
                            $statuses = [
                                'submitted' => ['class' => 'bg-info', 'icon' => 'fa-paper-plane'],
                                'pending' => ['class' => 'bg-warning', 'icon' => 'fa-clock'],
                                'on_progress' => ['class' => 'bg-primary', 'icon' => 'fa-spinner'],
                                'approved' => ['class' => 'bg-success', 'icon' => 'fa-check'],
                                'rejected' => ['class' => 'bg-danger', 'icon' => 'fa-times'],
                            ];

                            $statusData = $statuses[$leave->status] ?? 
                                ['class' => 'bg-secondary', 'icon' => 'fa-question'];

                            $user = auth()->user();
                        @endphp

                        <tr>
                            {{-- Correct pagination numbering --}}
                            <td>{{ $myleaves->firstItem() + $loop->index }}</td>

                            <td>
                                <span class="fw-semibold">
                                    {{ ucfirst($leave->request_type) }}
                                </span>
                            </td>

                            <td>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}
                                    -
                                    {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}
                                </small>
                            </td>

                            <td>
                                <span class="badge {{ $statusData['class'] }} px-3 py-2">
                                    <i class="fas {{ $statusData['icon'] }} me-1"></i>
                                    {{ strtoupper(str_replace('_', ' ', $leave->status)) }}
                                </span>
                            </td>

                            <td>
                                @if($leave->report_path)
                                    <a href="{{ asset('storage/' . $leave->report_path) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-download"></i>
                                    </a>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>

                            <td>
                                <div class="d-flex gap-1 flex-wrap">

                                    <a href="{{ route('leaves.show', $leave->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if($user->role === 'employee' && $leave->status === 'submitted')
                                        <a href="{{ route('leaves.edit', $leave->id) }}"
                                           class="btn btn-sm btn-outline-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('leaves.destroy', $leave->id) }}"
                                              method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Are you sure you want to delete this leave?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if(in_array($user->role, ['hod','admin']))
                                        <a href="{{ route('leaves.edit', $leave->id) }}"
                                           class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-check-circle"></i>
                                        </a>
                                    @endif

                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                    <p class="mb-0">No leave requests found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
        @if ($myleaves->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4">

        <div class="small text-muted">
            Showing {{ $myleaves->firstItem() }} 
            to {{ $myleaves->lastItem() }} 
            of {{ $myleaves->total() }} results
        </div>

        <nav>
            <ul class="pagination pagination-sm mb-0">

                {{-- Previous --}}
                <li class="page-item {{ $myleaves->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $myleaves->previousPageUrl() }}">
                        &laquo;
                    </a>
                </li>

                {{-- Page Numbers --}}
                @foreach ($myleaves->getUrlRange(1, $myleaves->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $myleaves->currentPage() ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    </li>
                @endforeach

                {{-- Next --}}
                <li class="page-item {{ !$myleaves->hasMorePages() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $myleaves->nextPageUrl() }}">
                        &raquo;
                    </a>
                </li>

            </ul>
        </nav>

    </div>
@endif


        </div>
    </div>
</div>
@endsection
