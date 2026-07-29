<?php

namespace App\Http\Controllers\customer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Display customer profile
     */
    public function index()
    {
        $user = Auth::user();

        return view('customer.profile', compact('user'));
    }

    /**
     * Update customer profile
     */
    public function update(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'current_password' => 'nullable|required_with:password',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($request->filled('password') && !Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo && file_exists(storage_path('app/public/profile_photos/' . $user->profile_photo))) {
                unlink(storage_path('app/public/profile_photos/' . $user->profile_photo));
            }

            $fileName = time() . '_' . uniqid() . '.' . $request->profile_photo->extension();
            $path = $request->profile_photo->storeAs('profile_photos', $fileName, 'public');

            if (!$path) {
                return back()->withErrors(['profile_photo' => 'Failed to save the profile photo. Please try again.'])->withInput();
            }

            $user->update(['profile_photo' => $fileName]);
        }

        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Update profile photo
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        /** @var User $user */
        $user = Auth::user();

        if ($request->hasFile('profile_photo')) {
            // Delete old profile photo if exists
            if ($user->profile_photo && file_exists(storage_path('app/public/profile_photos/' . $user->profile_photo))) {
                unlink(storage_path('app/public/profile_photos/' . $user->profile_photo));
            }

            // Store new photo
            $fileName = time() . '_' . uniqid() . '.' . $request->profile_photo->extension();
            $path = $request->profile_photo->storeAs('profile_photos', $fileName, 'public');

            if ($path) {
                $user->update(['profile_photo' => $fileName]);
                return back()->with('success', 'Profile photo updated successfully.');
            } else {
                return back()->withErrors(['profile_photo' => 'Failed to save the profile photo. Please try again.']);
            }
        }

        return back()->withErrors(['profile_photo' => 'No file uploaded.']);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        /** @var User $user */
        $user = Auth::user();

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }
}
