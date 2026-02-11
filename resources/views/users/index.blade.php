@extends('admin.layouts.app')

@section('title', 'Users List')
@section('page-title', 'User List')

@section('content')
<div class="container-fluid">

    {{-- Top Actions --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">user$user Requests Lists</h5>
        <a href="">Add New User</a>  
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- user$users Table --}}
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
                    @forelse($users as $user)
                        @php
                            $statuses = [
                                'submitted' => ['class' => 'bg-info', 'icon' => 'fa-paper-plane'],
                                'pending' => ['class' => 'bg-warning', 'icon' => 'fa-clock'],
                                'on_progress' => ['class' => 'bg-primary', 'icon' => 'fa-spinner'],
                                'approved' => ['class' => 'bg-success', 'icon' => 'fa-check'],
                                'rejected' => ['class' => 'bg-danger', 'icon' => 'fa-times'],
                            ];

                            $statusData = $statuses[$user->status] ?? 
                                ['class' => 'bg-secondary', 'icon' => 'fa-question'];

                            $user = auth()->user();
                        @endphp

                        <tr>
                            {{-- Correct pagination numbering --}}
                            <td>{{ $myuser$users->firstItem() + $loop->index }}</td>

                            <td>
                                <span class="fw-semibold">
                                    {{ ucfirst($user->request_type) }}
                                </span>
                            </td>

                            <td>
                                <small class="text-muted">
                                    {{ \Carbon\Carbon::parse($user->start_date)->format('d M Y') }}
                                    -
                                    {{ \Carbon\Carbon::parse($user->end_date)->format('d M Y') }}
                                </small>
                            </td>

                            <td>
                                <span class="badge {{ $statusData['class'] }} px-3 py-2">
                                    <i class="fas {{ $statusData['icon'] }} me-1"></i>
                                    {{ strtoupper(str_replace('_', ' ', $user->status)) }}
                                </span>
                            </td>

                            <td>
                                @if($user->report_path)
                                    <a href="{{ asset('storage/' . $user->report_path) }}"
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

                                    <a href="{{ route('user$users.show', $user->id) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    @if($user->role === 'employee' && $user->status === 'submitted')
                                        <a href="{{ route('user$users.edit', $user->id) }}"
                                           class="btn btn-sm btn-outline-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <form action="{{ route('user$users.destroy', $user->id) }}"
                                              method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Are you sure you want to delete this user$user?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if(in_array($user->role, ['hod','admin']))
                                        <a href="{{ route('user$users.edit', $user->id) }}"
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
                                    <p class="mb-0">No user$user requests found.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
        @if ($myuser$users->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4">

        <div class="small text-muted">
            Showing {{ $myuser$users->firstItem() }} 
            to {{ $myuser$users->lastItem() }} 
            of {{ $myuser$users->total() }} results
        </div>

        <nav>
            <ul class="pagination pagination-sm mb-0">

                {{-- Previous --}}
                <li class="page-item {{ $myuser$users->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $myuser$users->previousPageUrl() }}">
                        &laquo;
                    </a>
                </li>

                {{-- Page Numbers --}}
                @foreach ($myuser$users->getUrlRange(1, $myuser$users->lastPage()) as $page => $url)
                    <li class="page-item {{ $page == $myuser$users->currentPage() ? 'active' : '' }}">
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    </li>
                @endforeach

                {{-- Next --}}
                <li class="page-item {{ !$myuser$users->hasMorePages() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $myuser$users->nextPageUrl() }}">
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
