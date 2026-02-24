<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class ProfileController extends Controller{
    public function settings()
    {
        return view('profile.settings');
    }

    public function index()
    {
        return view('profile.index');
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'fname' => 'required|string|max:255',
            'mname' => 'nullable|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $user->fname = $request->input('fname');
        $user->mname = $request->input('mname');
        $user->lname = $request->input('lname');
        $user->email = $request->input('email');

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $user->profile_image = '/storage/' . $path;
        }

        if ($request->filled('password')) {
            $user->password = bcrypt($request->input('password'));
        }

        $user->save();

        return redirect()->route('profile')->with('success', 'Profile updated successfully.');
    }

}
