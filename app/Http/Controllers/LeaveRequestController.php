<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use TCPDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    /**
     * Display all leave requests.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $leaveRequests = LeaveRequest::with('user.department')->latest()->get();
        } elseif ($user->role === 'hod') {
            $leaveRequests = LeaveRequest::whereHas('user', fn($q) => $q->where('department_id', $user->department_id))
                ->with('user.department')->latest()->get();
        } else {
            abort(403, 'Unauthorized');
        }

        return view('leaves.index', compact('leaveRequests'));
    }

    /**
     * Display staff in HOD's department.
     */
    public function staff()
    {
        $user = Auth::user();

        if ($user->role !== 'hod') {
            abort(403, 'Unauthorized');
        }

        $staff = User::where('department_id', $user->department_id)
            ->where('role', 'employee')
            ->get();

        return view('leaves.staff', compact('staff'));
    }

    /**
     * Display approved leave requests.
     */
    public function approved()
    {
        $user = Auth::user();

        $query = LeaveRequest::with('user.department')->where('status', 'approved');

        if ($user->role === 'hod') {
            $query->whereHas('user', fn($q) => $q->where('department_id', $user->department_id));
        } elseif ($user->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $leaveRequests = $query->latest()->get();

        return view('leaves.approved', compact('leaveRequests'));
    }

    /**
     * Display pending/on-progress leave requests.
     */
    public function pending()
    {
        $user = Auth::user();

        $query = LeaveRequest::with('user.department')
            ->where('status', 'submitted');

        if ($user->role === 'hod') {
            $query->whereHas('user', fn($q) => $q->where('department_id', $user->department_id));
        } elseif ($user->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $leaveRequests = $query->latest()->get();

        return view('leaves.pending', compact('leaveRequests'));
    }

    public function rejected(){
        $user = Auth::user();

        $query = LeaveRequest::with('user.department')
            ->where('status', 'rejected');

        if ($user->role === 'hod') {
            $query->whereHas('user', fn($q) => $q->where('department_id', $user->department_id));
        } elseif ($user->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        $leaveRequests = $query->latest()->get();

        return view('leaves.rejected', compact('leaveRequests'));
    }

    /**
     * Show form to create a leave request.
     */
    public function create()
    {
        return view('leaves.create');
    }

    /**
     * Store new leave request.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_type' => 'required|string',
            'start_date' => 'required|date|today_or_future',
            'end_date' => 'required|date|after_or_equal:start_date',
            'report_path' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'reasons' => 'nullable|string',
            'destination' => 'nullable|string',
        ], [
            'start_date.today_or_future' => 'Leave start date cannot be in the past.',
            'end_date.after_or_equal' => 'End date must be the same or after the start date.',
        ]);

        $days = Carbon::parse($validated['start_date'])->diffInDays(Carbon::parse($validated['end_date'])) + 1;
        if ($days > 14) {
            return back()->withInput()->with('error', 'Leave request exceeds 14 days.');
        }

        $user = Auth::user();

        // Check overlapping leaves
        $overlap = LeaveRequest::where('user_id', $user->id)
            ->whereIn('status', ['submitted','pending','on_progress','approved'])
            ->where(function($q) use ($validated) {
                $q->whereBetween('start_date', [$validated['start_date'],$validated['end_date']])
                  ->orWhereBetween('end_date', [$validated['start_date'],$validated['end_date']])
                  ->orWhere(fn($q2) => $q2->where('start_date','<=',$validated['start_date'])
                                           ->where('end_date','>=',$validated['end_date']));
            })->first();

        if ($overlap) {
            return back()->withInput()->with('error', 'Overlapping leave exists from ' .
                Carbon::parse($overlap->start_date)->format('d M Y') . ' to ' .
                Carbon::parse($overlap->end_date)->format('d M Y'));
        }

        $validated['user_id'] = $user->id;
        $validated['status'] = 'submitted';

        if ($request->hasFile('report_path')) {
            $validated['report_path'] = $request->file('report_path')->store('reports', 'public');
        }

        LeaveRequest::create($validated);

        return redirect()->route('leaves.showmy')->with('success','Leave request submitted.');
    }

    /**
     * Show specific leave request.
     */
    public function show($id)
    {
        $leaveRequest = LeaveRequest::with('user.department')->findOrFail($id);
        $user = Auth::user();

        if (!$leaveRequest->user) abort(404, 'Associated user not found.');
        if ($user->id !== $leaveRequest->user_id && $user->role === 'employee') abort(403);
        if ($user->role === 'hod' && $user->department_id !== $leaveRequest->user->department_id) abort(403);

        return view('leaves.show', compact('leaveRequest'));
    }

    /**
     * Show authenticated user's leave requests.
     */
    public function showMyLeave(Request $request)
    {
        $query = LeaveRequest::where('user_id', auth()->id());

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('request_type')) $query->where('request_type','LIKE','%'.$request->request_type.'%');
        if ($request->filled('from_date')) $query->where('start_date','>=',$request->from_date);
        if ($request->filled('to_date')) $query->where('end_date','<=',$request->to_date);

        $myleaves = $query->latest()->get();
        return view('leaves.showmy', compact('myleaves'));
    }

    /**
     * Edit leave request.
     */
    public function edit($id)
    {
        $leave = LeaveRequest::with('user.department')->findOrFail($id);
        $user = Auth::user();

        if (!$leave->user) abort(404);
        if ($user->id !== $leave->user_id && !in_array($user->role,['hod','admin'])) abort(403);
        if ($user->role === 'hod' && $user->department_id !== $leave->user->department_id) abort(403);

        return view('leaves.edit', compact('leave'));
    }

    /**
     * Update leave request.
     */
    public function update(Request $request, $id)
    {
        $leave = LeaveRequest::with('user.department')->findOrFail($id);
        $user = Auth::user();

        if (!$leave->user) abort(404);
        if ($user->role === 'hod' && $user->department_id !== $leave->user->department_id) {
            return back()->with('error','Cannot update leaves outside your department.');
        }

        $validated = $request->validate([
            'status' => 'required|in:submitted,pending,on_progress,approved,rejected',
            'report_path' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'hod_signature' => 'nullable|string',
            'hod_remarks' => 'nullable|string',
            'admin_signature' => 'nullable|string',
            'admin_remarks' => 'nullable|string',
        ]);

        if ($request->hasFile('report_path')) {
            $validated['report_path'] = $request->file('report_path')->store('reports','public');
        }

        if ($user->role === 'hod' && $request->filled('hod_signature')) {
            $validated['hod_signature'] = $request->hod_signature;
            $validated['hod_signed_at'] = now();
            $validated['status'] = 'on_progress';
        }

        if ($user->role === 'admin' && $request->filled('admin_signature')) {
            $validated['admin_signature'] = $request->admin_signature;
            $validated['admin_signed_at'] = now();
            $validated['status'] = 'approved';
        }

        $leave->update($validated);

        return redirect()->route('leaves.show', $leave->id)->with('success','Leave request updated.');
    }

    /**
     * Delete leave request.
     */
    public function destroy($id)
    {
        $leave = LeaveRequest::with('user.department')->findOrFail($id);
        $user = Auth::user();

        if (!$leave->user) abort(404);

        $isOwner = $user->id === $leave->user_id;
        $isAdmin = $user->role === 'admin';
        $isHod = $user->role === 'hod' && $user->department_id === $leave->user->department_id;

        if (!$isOwner && !$isAdmin && !$isHod) abort(403);

        $leave->delete();
        return redirect()->route('leaves.index')->with('success','Leave request deleted.');
    }



<<<<<<< HEAD
=======
        $pdf = new TCPDF('P','mm','A4',true,'UTF-8',false);
        $pdf->SetMargins(15,15,15);
        $pdf->SetAutoPageBreak(true,15);
        $pdf->AddPage();

        $pdf->SetFont('helvetica','B',20);
        $pdf->Cell(0,15,'MINISTRY OF HEALTH',0,1,'C');
        $pdf->SetFont('helvetica','',12);
        $pdf->Cell(0,10,'Leave Request Form',0,1,'C');
        $pdf->Ln(10);

        $pdf->SetFont('helvetica','B',11);
        $pdf->Cell(0,10,'EMPLOYEE DETAILS',0,1,'L');
        $pdf->SetFont('helvetica','',11);

        $details = "Employee Name: {$leave->user->full_name}\n";
        $details .= "Email: {$leave->user->email}\n";
        $details .= "Department: {$leave->user->department->name}\n";
        $details .= "Leave Type: {$leave->request_type}\n";
        $details .= "Start Date: ".Carbon::parse($leave->start_date)->format('d M Y')."\n";
        $details .= "End Date: ".Carbon::parse($leave->end_date)->format('d M Y')."\n";
        $details .= "Duration: ".(Carbon::parse($leave->start_date)->diffInDays(Carbon::parse($leave->end_date))+1)." day(s)\n";
        $details .= "Status: ".strtoupper(str_replace('_',' ',$leave->status))."\n";

        $pdf->MultiCell(0,5,$details,0,'L');
        $pdf->Ln(5);

        // Signatures
        $pdf->SetFont('helvetica','B',11);
        $pdf->Cell(0,10,'APPROVALS',0,1,'L');
        $pdf->SetFont('helvetica','',10);

        foreach (['hod','admin'] as $role) {
            $sigField = "{$role}_signature";
            $remarksField = "{$role}_remarks";
            $signedAtField = "{$role}_signed_at";

            if ($leave->$sigField) {
                $pdf->Ln(5);
                $pdf->Cell(0,10, strtoupper($role).' Approval:',0,1);

                if (strpos($leave->$sigField,'data:image')===0) {
                    list(,$data) = explode(',', explode(';',$leave->$sigField)[1]);
                    $data = base64_decode($data);
                    $tmpFile = sys_get_temp_dir()."/{$role}_sig.png";
                    file_put_contents($tmpFile,$data);
                    $pdf->Image($tmpFile,20,$pdf->GetY(),40,20);
                    $pdf->Ln(20);
                    unlink($tmpFile);
                }

                if ($leave->$remarksField) $pdf->Cell(0,5,"Remarks: ".$leave->$remarksField,0,1);
                $pdf->Cell(0,5,"Approved on: ".Carbon::parse($leave->$signedAtField)->format('d M Y H:i:s'),0,1);
            }
        }

        $pdf->Ln(15);
        $pdf->SetFont('helvetica','',8);
        $pdf->Cell(0,10,'This is an electronically signed document.',0,1,'C');
        $pdf->Cell(0,10,'Generated on: '.now()->format('d M Y H:i:s'),0,1,'C');

        return $pdf->Output('leave_request_'.$leave->id.'.pdf','D');
    }

>>>>>>> e394fef3e643a070c40e624698ac83d910a9a5d7
    public function approve(Request $request, $id)
    {
        $leave = LeaveRequest::with('user.department')->findOrFail($id);
        $user = Auth::user();

        if (!$leave->user) abort(404);
        if ($user->role === 'hod' && $user->department_id !== $leave->user->department_id) {
            return back()->with('error','Cannot approve leaves outside your department.');
        }

        $signaturePath = $leave->admin_signature;
        if ($request->hasFile('signature_file')) {
            $signaturePath = $request->file('signature_file')->store('signatures', 'public');
        }

        $leave->update([
            'status' => 'approved',
            'admin_signature' => $signaturePath,
            'admin_signed_at' => now(),
        ]);

        // Log to LeaveHistory
        \App\Models\LeaveHistory::create([
            'leave_request_id' => $leave->id,
            'user_id' => $user->id,
            'action' => 'approved',
            'remarks' => $request->input('hod_remarks'),
            'created_at' => now(),
        ]);

        // Log to AuditLog
        \App\Models\AuditLog::create([
            'user_id' => $user->id,
            'action' => 'approve',
            'description' => 'Approved leave request ID: ' . $leave->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('leaves.show', $leave->id)->with('success','Leave request approved.');
    }

    public function reject(Request $request, $id)
    {
        $leave = LeaveRequest::with('user.department')->findOrFail($id);
        $user = Auth::user();

        if (!$leave->user) abort(404);
        if ($user->role === 'hod' && $user->department_id !== $leave->user->department_id) {
            return back()->with('error','Cannot reject leaves outside your department.');
        }

        $leave->update([
            'status' => 'rejected',
            'admin_signature' => $leave->admin_signature,
            'admin_signed_at' => now(),
        ]);

        // Log to LeaveHistory
        \App\Models\LeaveHistory::create([
            'leave_request_id' => $leave->id,
            'user_id' => $user->id,
            'action' => 'rejected',
            'remarks' => $request->input('remarks'),
            'created_at' => now(),
        ]);

        // Log to AuditLog
        \App\Models\AuditLog::create([
            'user_id' => $user->id,
            'action' => 'reject',
            'description' => 'Rejected leave request ID: ' . $leave->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('leaves.show', $leave->id)->with('success','Leave request rejected.');
    }

    
}
