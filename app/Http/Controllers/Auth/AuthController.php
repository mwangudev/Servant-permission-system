<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Show login page.
     */
    public function index()
    {
        return view('auth.login');
    }

    /**
     * Handle login form submission.
     */
    public function submit(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            // Redirect to the dashboard route
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Show registration page.
     */
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Handle registration form submission.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'mname' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed|min:6',
            'gender' => 'required|in:male,female,other',
            'dob' => 'required|date',
        ]);

        User::create([
            'fname' => $request->fname,
            'mname' => $request->mname,
            'lname' => $request->lname,
            'email' => $request->email,
            'gender' => $request->gender,
            'dob' => $request->dob,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('login')->with('status', 'Account created successfully! You can now login.');
    }

    /**
     * Logout the user.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    /**
     * Show dashboard page for logged-in users.
     */
    public function dashboard()
{
    // Fetch leave requests with user relation to avoid N+1 problem
    $leaveRequests = \App\Models\LeaveRequest::with('user')->get();

    // Group by request_type and count
    $chartData = $leaveRequests->groupBy('request_type')->map->count();

    // Labels and values for chart
    $chartLabels = $chartData->keys()->toArray();   // ['sick', 'annual', ...]
    $chartValues = $chartData->values()->toArray(); // [5, 3, ...]

    // Stats counts
    $submittedCount = $leaveRequests->where('status', 'submitted')->count();
    $pendingCount   = $leaveRequests->where('status', 'pending')->count();
    $approvedCount  = $leaveRequests->where('status', 'approved')->count();
    $rejectedCount  = $leaveRequests->where('status', 'rejected')->count();

    // Latest pending requests
    $pendingRequests = $leaveRequests->where('status', 'pending')->take(6);

    return view('admin.dashboard', compact(
        'chartLabels',
        'chartValues',
        'submittedCount',
        'pendingCount',
        'approvedCount',
        'rejectedCount',
        'pendingRequests',
        'leaveRequests' // Optional if you need all leave requests in the view
    ));
}

}
