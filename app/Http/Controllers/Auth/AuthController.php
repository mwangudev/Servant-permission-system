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
        $validated = $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'mname' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed|min:6',
            'gender' => 'required|in:male,female,other',
            'dob' => 'required|date',
        ]);

        User::create([
            'fname' => $validated['fname'],
            'mname' => $validated['mname'],
            'lname' => $validated['lname'],
            'email' => $validated['email'],
            'gender' => $validated['gender'],
            'dob' => $validated['dob'],
            'password' => Hash::make($validated['password']),
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
    $userId = Auth::id();
    $currentUser = Auth::user();

    // Fetch all leave requests with user relation
    $leaveRequests = \App\Models\LeaveRequest::with('user')->get();

    /*
    |--------------------------------------------------------------------------
    | USER-SPECIFIC STATS
    |--------------------------------------------------------------------------
    */

    $submittedCount = $leaveRequests
        ->where('user_id', $userId)
        ->where('status', 'submitted')
        ->count();

    $pendingCount = $leaveRequests
        ->where('user_id', $userId)
        ->whereIn('status', ['pending', 'on_progress'])
        ->count();

    $approvedCount = $leaveRequests
        ->where('user_id', $userId)
        ->where('status', 'approved')
        ->count();

    $rejectedCount = $leaveRequests
        ->where('user_id', $userId)
        ->where('status', 'rejected')
        ->count();


    /*
    |--------------------------------------------------------------------------
    | ADMIN / GLOBAL STATS
    |--------------------------------------------------------------------------
    */

    $allSubmittedCount = $leaveRequests
        ->where('status', 'submitted')
        ->count();

    $allPendingCount = $leaveRequests
        ->whereIn('status', ['pending', 'on_progress'])
        ->count();

    $allApprovedCount = $leaveRequests
        ->where('status', 'approved')
        ->count();

    $allRejectedCount = $leaveRequests
        ->where('status', 'rejected')
        ->count();


    /*
    |--------------------------------------------------------------------------
    | HOD DEPARTMENT USER COUNT
    |--------------------------------------------------------------------------
    */

    $hodDepartmentUserCount = 0;

    if ($currentUser->role === 'hod' && $currentUser->department_id) {
        $hodDepartmentUserCount = User::where(
            'department_id',
            $currentUser->department_id
        )->count();
    }


    /*
    |--------------------------------------------------------------------------
    | RECENT 3 WAITING YOUR APPROVAL (FOR ADMIN / HOD)
    |--------------------------------------------------------------------------
    */

    $recentPendingLeaves = collect();

    if (\in_array($currentUser->role, ['admin', 'hod'])) {

        $recentPendingLeaves = $leaveRequests
            ->whereIn('status', ['pending', 'on_progress'])
            ->sortByDesc('created_at')
            ->take(3)
            ->values(); // reset keys
    }


    /*
    |--------------------------------------------------------------------------
    | CHART DATA (Grouped by request_type)
    |--------------------------------------------------------------------------
    */

    $chartData = $leaveRequests
        ->groupBy('request_type')
        ->map(fn ($group) => $group->count());

    $chartLabels = $chartData->keys()->toArray();
    $chartValues = $chartData->values()->toArray();


    return view('admin.dashboard', compact(
        'submittedCount',
        'pendingCount',
        'approvedCount',
        'rejectedCount',

        'allSubmittedCount',
        'allPendingCount',
        'allApprovedCount',
        'allRejectedCount',

        'hodDepartmentUserCount',

        'recentPendingLeaves',

        'chartLabels',
        'chartValues',

        'leaveRequests'
    ));
}

}
