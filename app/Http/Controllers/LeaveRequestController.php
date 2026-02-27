<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Models\LeaveHistory;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $leaveRequests = LeaveRequest::with('user.department')->latest()->paginate(15);
        } elseif ($user->role === 'hod') {
            $leaveRequests = LeaveRequest::whereHas('user', fn($q) => $q->where('department_id', $user->department_id))
                ->with('user.department')->latest()->paginate(15);
        } else {
            abort(403, 'Unauthorized');
        }

        return view('leaves.index', compact('leaveRequests'));
    }

    public function staff()
    {
        $user = Auth::user();
        if ($user->role !== 'hod') abort(403);

        $staff = User::where('department_id', $user->department_id)
            ->where('role', 'employee')
            ->get();

        return view('leaves.staff', compact('staff'));
    }

    public function approved()
    {
        $user = Auth::user();
        $query = LeaveRequest::with('user.department')->where('status', 'approved');

        if ($user->role === 'hod') {
            $query->whereHas('user', fn($q) => $q->where('department_id', $user->department_id));
        } elseif ($user->role !== 'admin') {
            abort(403);
        }

        $leaveRequests = $query->latest()->paginate(15);
        return view('leaves.approved', compact('leaveRequests'));
    }

    public function pending()
    {
        $user = Auth::user();
        $query = LeaveRequest::with('user.department')->where('status', 'submitted');

        if ($user->role === 'hod') {
            $query->whereHas('user', fn($q) => $q->where('department_id', $user->department_id));
        } elseif ($user->role !== 'admin') {
            abort(403);
        }

        $leaveRequests = $query->latest()->paginate(15);
        return view('leaves.pending', compact('leaveRequests'));
    }

    public function rejected()
    {
        $user = Auth::user();
        $query = LeaveRequest::with('user.department')->where('status', 'rejected');

        if ($user->role === 'hod') {
            $query->whereHas('user', fn($q) => $q->where('department_id', $user->department_id));
        } elseif ($user->role !== 'admin') {
            abort(403);
        }

        $leaveRequests = $query->latest()->paginate(15);
        return view('leaves.rejected', compact('leaveRequests'));
    }

    public function create()
    {
        return view('leaves.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_type'     => 'required|string',
            'start_date'       => 'required|date|today_or_future',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'report_path'      => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'reasons'          => 'nullable|string',
            'destination'      => 'nullable|string',
            'request_category' => 'nullable|string',
        ], [
            'start_date.today_or_future' => 'Leave cannot start in the past.',
        ]);

        $days = Carbon::parse($validated['start_date'])->diffInDays(Carbon::parse($validated['end_date'])) + 1;
        if ($days > 14) {
            return back()->withInput()->with('error', 'Maximum allowed leave is 14 days.');
        }

        $user = Auth::user();

        $overlap = LeaveRequest::where('user_id', $user->id)
            ->whereIn('status', ['submitted', 'pending', 'approved'])
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                  ->orWhereBetween('end_date',   [$validated['start_date'], $validated['end_date']])
                  ->orWhere(fn($q2) => $q2->where('start_date', '<=', $validated['start_date'])
                                           ->where('end_date',   '>=', $validated['end_date']));
            })->exists();

        if ($overlap) {
            return back()->withInput()->with('error', 'You already have an overlapping leave request.');
        }

        $data = $validated;
        $data['user_id'] = $user->id;
        $data['status']  = 'submitted';

        if ($request->hasFile('report_path')) {
            $data['report_path'] = $request->file('report_path')->store('reports', 'public');
        }

        LeaveRequest::create($data);

        return redirect()->route('leaves.showmy')->with('success', 'Leave request submitted successfully.');
    }

    public function show($id)
    {
        $leaveRequest = LeaveRequest::with('user.department')->findOrFail($id);
        $user = Auth::user();

        if ($user->role === 'employee' && $user->id !== $leaveRequest->user_id) {
            abort(403);
        }
        if ($user->role === 'hod' && $leaveRequest->user->department_id !== $user->department_id) {
            abort(403);
        }

        return view('leaves.show', compact('leaveRequest'));
    }

    public function showMyLeave(Request $request)
    {
        $query = LeaveRequest::where('user_id', auth()->id());

        if ($request->filled('status'))           $query->where('status', $request->status);
        if ($request->filled('request_type'))     $query->where('request_type', 'LIKE', '%' . $request->request_type . '%');
        if ($request->filled('request_category')) $query->where('request_category', 'LIKE', '%' . $request->request_category . '%');
        if ($request->filled('from_date'))        $query->whereDate('start_date', '>=', $request->from_date);
        if ($request->filled('to_date'))          $query->whereDate('end_date',   '<=', $request->to_date);

        $myleaves = $query->latest()->paginate(10);
        return view('leaves.showmy', compact('myleaves'));
    }

    public function edit($id)
    {
        $leave = LeaveRequest::with('user.department')->findOrFail($id);
        $user  = Auth::user();

        if ($user->role === 'employee' && $user->id !== $leave->user_id) abort(403);
        if ($user->role === 'hod' && $leave->user->department_id !== $user->department_id) abort(403);

        return view('leaves.edit', compact('leave'));
    }

    public function update(Request $request, $id)
    {
        $leave = LeaveRequest::findOrFail($id);
        $user  = Auth::user();

        if ($user->role === 'hod' && $leave->user->department_id !== $user->department_id) {
            return back()->with('error', 'You can only manage leaves in your department.');
        }

        $validated = $request->validate([
            'report_path'     => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'hod_remarks'     => 'nullable|string|max:1500',
            'admin_remarks'   => 'nullable|string|max:1500',
        ]);

        if ($request->hasFile('report_path')) {
            $validated['report_path'] = $request->file('report_path')->store('reports', 'public');
        }

        // Prevent overwriting with empty strings
        if (isset($validated['hod_remarks']) && trim($validated['hod_remarks']) === '') {
            unset($validated['hod_remarks']);
        }
        if (isset($validated['admin_remarks']) && trim($validated['admin_remarks']) === '') {
            unset($validated['admin_remarks']);
        }

        $leave->update($validated);

        return redirect()->route('leaves.show', $leave->id)->with('success', 'Leave request updated.');
    }

    public function destroy($id)
    {
        $leave = LeaveRequest::findOrFail($id);
        $user  = Auth::user();

        $canDelete = $user->id === $leave->user_id ||
                     $user->role === 'admin' ||
                     ($user->role === 'hod' && $leave->user->department_id === $user->department_id);

        if (!$canDelete) abort(403);

        $leave->delete();

        return redirect()->route('leaves.index')->with('success', 'Leave request deleted.');
    }

    public function downloadPDF($id)
    {
        $leaveRequest = LeaveRequest::with('user.department')->findOrFail($id);

        $lastLeave = LeaveRequest::where('user_id', $leaveRequest->user_id)
            ->where('id', '<', $leaveRequest->id)
            ->where('start_date', '<', $leaveRequest->start_date)
            ->whereIn('status', ['approved', 'rejected'])
            ->orderBy('start_date', 'desc')
            ->first();

        if ($leaveRequest->status !== 'approved') {
            abort(403, 'Only approved leaves can be downloaded as PDF.');
        }

        $pdf = Pdf::loadView('leaves.pdf', compact('leaveRequest', 'lastLeave'));

        return $pdf->download("leave_request_{$id}.pdf");
    }



    // ... kodi zingine ...

    public function approve(Request $request, $id)
    {
        $leave = LeaveRequest::findOrFail($id);
        $user  = Auth::user();

        if ($user->role === 'hod' && $leave->user->department_id !== $user->department_id) {
            return back()->with('error', 'Cannot approve leaves outside your department.');
        }

        if (!in_array($user->role, ['hod', 'admin'])) {
            abort(403);
        }

        // 1. 'signature' isnot 'file', is 'string' (Base64 text)
        $validated = $request->validate([
            'signature'      => 'nullable|string',
            'hod_remarks'    => 'nullable|string|max:1500',
            'admin_remarks'  => 'nullable|string|max:1500',
        ]);

        // 2. Change Base64 Text to image (.png)
        $signaturePath = null;
        if ($request->filled('signature')) {
            $image_parts = explode(";base64,", $request->signature);

            if (count($image_parts) == 2) {
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1]; // inatafuta kama ni png
                $image_base64 = base64_decode($image_parts[1]);

                // Tengeneza jina la faili
                $fileName = 'signature_' . $user->role . '_' . time() . '.' . $image_type;
                $signaturePath = 'signatures/leaves/' . $fileName;

                // Save in storage (storage/app/public/signatures/leaves/)
                Storage::disk('public')->put($signaturePath, $image_base64);
            }
        }


        DB::transaction(function () use ($leave, $user, $validated, $signaturePath, $request) {
            $remarksKey = $user->role === 'hod' ? 'hod_remarks' : 'admin_remarks';
            $newRemarks = $validated[$remarksKey] ?? null;

            $updateData = [];

            if ($user->role === 'hod') {
                if ($leave->status !== 'submitted') {
                    throw new \Exception('Leave must be in submitted status for HOD approval.');
                }

                $updateData = [
                    'status'         => 'pending',
                    'hod_signature'  => $signaturePath, // Hapa inasave ile njia (path) kwenye DB
                    'hod_signed_at'  => now(),
                ];

                if ($newRemarks !== null && trim($newRemarks) !== '') {
                    $updateData['hod_remarks'] = $newRemarks;
                }

                $action = 'Approved by HOD';
            } else {
                if ($leave->status !== 'pending') {
                    throw new \Exception('Leave must be in pending status for Admin approval.');
                }

                $updateData = [
                    'status'          => 'approved',
                    'admin_signature' => $signaturePath, // Hapa inasave ile njia (path) kwenye DB
                    'admin_signed_at' => now(),
                ];

                if ($newRemarks !== null && trim($newRemarks) !== '') {
                    $updateData['admin_remarks'] = $newRemarks;
                }

                $action = 'Approved By Administrator';
            }

            $leave->update($updateData);

            LeaveHistory::create([
                'leave_request_id' => $leave->id,
                'user_id'          => $user->id,
                'action'           => $action,
                'remarks'          => $newRemarks ?: 'No remarks added during approval',
            ]);

            AuditLog::create([
                'user_id'     => $user->id,
                'action'      => 'approve',
                'description' => ucfirst($action) . " leave request ID: {$leave->id}",
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ]);
        });

        $message = $user->role === 'hod'
            ? 'Leave approved by HOD. Awaiting final admin approval.'
            : 'Leave request fully approved.';

        return redirect()->route('leaves.show', $leave->id)
            ->with('success', $message);
    }


    public function onprogress()
    {
        $user = Auth::user();
        $query = LeaveRequest::with('user.department')->where('status', 'pending');

        if ($user->role === 'hod') {
            $query->whereHas('user', fn($q) => $q->where('department_id', $user->department_id));
        } elseif ($user->role !== 'admin') {
            abort(403);
        }

        $leaveRequests = $query->latest()->paginate(15);
        return view('leaves.onprogress', compact('leaveRequests'));
    }

    public function reject(Request $request, $id)
    {
        $leave = LeaveRequest::findOrFail($id);
        $user  = Auth::user();

        if ($user->role === 'hod' && $leave->user->department_id !== $user->department_id) {
            return back()->with('error', 'Cannot reject leaves outside your department.');
        }

        if (!in_array($user->role, ['hod', 'admin'])) {
            abort(403);
        }

        $validated = $request->validate([
            'remarks' => 'nullable|string|max:1500',
        ]);

        DB::transaction(function () use ($leave, $user, $validated, $request) {
            $remarksKey = $user->role === 'hod' ? 'hod_remarks' : 'admin_remarks';
            $newRemarks = $validated['remarks'] ?? null;

            $updateData = [
                'status' => 'rejected',
            ];

            if ($newRemarks !== null && trim($newRemarks) !== '') {
                $updateData[$remarksKey] = $newRemarks;
            }

            $leave->update($updateData);

            LeaveHistory::create([
                'leave_request_id' => $leave->id,
                'user_id'          => $user->id,
                'action'           => 'rejected',
                'remarks'          => $newRemarks ?: 'No rejection reason provided',
            ]);

            AuditLog::create([
                'user_id'     => $user->id,
                'action'      => 'reject',
                'description' => 'Rejected leave request ID: ' . $leave->id,
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ]);
        });

        return redirect()->route('leaves.show', $leave->id)
            ->with('success', 'Leave request rejected.');
    }
}
