<?php

namespace App\Http\Controllers\seller;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Display seller profile
     */
    public function index()
    {
        $seller = Auth::user();
        return view('seller.profile', compact('seller'));
    }

    /**
     * Update seller profile
     */
    public function update(Request $request)
    {
        /** @var \App\Models\User $seller */
        $seller = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $seller->id,
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'current_password' => 'nullable|required_with:password|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            if (!$request->filled('current_password') || !Hash::check($request->current_password, $seller->password)) {
                return redirect()->back()
                    ->withErrors(['current_password' => 'Current password is incorrect.'])
                    ->withInput();
            }
            $data['password'] = Hash::make($request->password);
        }

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Ensure directory exists
            $directory = 'public/profile_photos';
            if (!Storage::exists($directory)) {
                Storage::makeDirectory($directory);
            }

            // Delete old photo if exists
            if ($seller->profile_photo && Storage::exists($directory . '/' . $seller->profile_photo)) {
                Storage::delete($directory . '/' . $seller->profile_photo);
            }

            // Store new photo
            $fileName = time() . '_' . $seller->id . '.' . $request->profile_photo->extension();
            $stored = Storage::disk('public')->putFileAs('profile_photos', $request->file('profile_photo'), $fileName);

            if ($stored) {
                $data['profile_photo'] = $fileName;
            }
        }

        $seller->update($data);

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }
}
