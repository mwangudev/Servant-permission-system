<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class LeaveRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $leaveRequest=LeaveRequest::all()
        ->latest()
        ->paginate();
        return view('leave.index',compact('leaveRequest'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('leaves.create');
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
{
    $validated = $request->validate([
        'request_type' => 'required|string',
        'start_date'   => 'required|date',
        'end_date'     => 'required|date|after_or_equal:start_date',
        'report_path'  => 'nullable|file|mimes:pdf,jpg,png|max:2048',
    ]);

    $validated['user_id'] = Auth::user()->id;

    // Handle file upload
    if ($request->hasFile('report_path')) {
        $path = $request->file('report_path')->store('reports', 'public');
        $validated['report_path'] = $path;
    }

    LeaveRequest::create($validated);

    return redirect()->route('leaves.showmy')->with('success', 'Leave request submitted successfully.');
}


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $leaveRequest=LeaveRequest::findOrFail($id);
        return view('leaves.show',compact('leaveRequest'));
    }

    //my leave
    public function showMyLeave()
    {
        
        $myleaves = LeaveRequest::where('user_id',auth()->id())
        ->latest()
        ->paginate(10);
        return view('leaves.showmy', compact('myleaves'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveRequest $leaveRequest)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        //
    }
}
