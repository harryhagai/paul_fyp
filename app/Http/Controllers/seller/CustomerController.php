<?php

namespace App\Http\Controllers\seller;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    // Display all customers with pagination
    public function index(Request $request)
    {
        $search = $request->input('search');
        $isAdmin = Auth::check() && Auth::user()->role === 'admin';

        $query = User::query()
            ->when(!$isAdmin, function ($q) {
                $q->whereIn('role', ['customer', 'seller']);
            })
            ->when($search, function ($q) use ($search, $isAdmin) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
                if ($isAdmin) {
                    $q->orWhere('role', 'like', '%' . $search . '%');
                }
            })
            ->orderBy('created_at', 'desc');

        $customers = $query->paginate(10);

        return view('seller.customers', compact('customers'));
    }

    // Store a new customer
    public function store(Request $request)
    {
        $isAdmin = Auth::check() && Auth::user()->role === 'admin';

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users,email',
            'password' => 'required|string|min:8',
            'password_confirmation' => 'required|string|same:password',
            'role' => $isAdmin ? 'required|in:admin,seller,customer' : 'nullable|in:customer,seller',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $customer = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $isAdmin ? $request->role : ($request->role ?: 'customer'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully!',
                'customer' => $customer
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer: ' . $e->getMessage()
            ], 500);
        }
    }

    // Show customer details
    public function show($id)
    {
        $isAdmin = Auth::check() && Auth::user()->role === 'admin';
        $customer = User::when(!$isAdmin, function ($q) {
            $q->whereIn('role', ['customer', 'seller']);
        })->wherePublicIdOrId($id)->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'customer' => $customer
        ]);
    }

    // Update customer
    public function update(Request $request, $id)
    {
        $isAdmin = Auth::check() && Auth::user()->role === 'admin';
        $customer = User::when(!$isAdmin, function ($q) {
            $q->whereIn('role', ['customer', 'seller']);
        })->wherePublicIdOrId($id)->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users,email,' . $customer->id,
            'password' => 'nullable|string|min:8',
            'password_confirmation' => 'nullable|string|same:password',
            'role' => $isAdmin ? 'required|in:admin,seller,customer' : 'nullable|in:customer,seller',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = [
                'name' => $request->name,
                'email' => $request->email,
            ];
            if ($isAdmin || $request->filled('role')) {
                $data['role'] = $request->role;
            }

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $customer->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Customer updated successfully!',
                'customer' => $customer
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update customer: ' . $e->getMessage()
            ], 500);
        }
    }

    // Delete customer
    public function destroy($id)
    {
        $isAdmin = Auth::check() && Auth::user()->role === 'admin';
        $customer = User::when(!$isAdmin, function ($q) {
            $q->whereIn('role', ['customer', 'seller']);
        })->wherePublicIdOrId($id)->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        try {
            $customer->delete();

            return response()->json([
                'success' => true,
                'message' => 'Customer deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete customer: ' . $e->getMessage()
            ], 500);
        }
    }
}
