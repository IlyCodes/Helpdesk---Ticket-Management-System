<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth; // To prevent admin from deleting self

class AdminUserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index(Request $request)
    {
        $query = User::query();
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(fn($q) => $q->where('name', 'like', "%{$searchTerm}%")->orWhere('email', 'like', "%{$searchTerm}%"));
        }
        if ($request->has('is_active') && $request->is_active !== '') { // Handle '0' for inactive
            $query->where('is_active', (bool)$request->is_active);
        }

        $users = $query->orderBy('name')->paginate(15);
        $roles = ['admin', 'agent', 'client'];
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = ['admin', 'agent', 'client'];
        $specializations = ['General Support', 'Technical Level 1', 'Technical Level 2', 'Billing', 'Sales', 'Hardware', 'Software'];
        return view('admin.users.create', compact('roles', 'specializations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', Rule::in(['admin', 'agent', 'client'])],
            'specialization' => ['nullable', 'string', 'max:255', Rule::requiredIf($request->role === 'agent')],
            'is_active' => ['sometimes', 'boolean'], // Add validation for is_active
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'specialization' => $request->role === 'agent' ? $request->specialization : null,
            'is_active' => $request->boolean('is_active', true), // Default to true if not provided
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $roles = ['admin', 'agent', 'client'];
        $specializations = ['General Support', 'Technical Level 1', 'Technical Level 2', 'Billing', 'Sales', 'Hardware', 'Software'];
        return view('admin.users.edit', compact('user', 'roles', 'specializations'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', Rule::in(['admin', 'agent', 'client'])],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'specialization' => ['nullable', 'string', 'max:255', Rule::requiredIf($request->role === 'agent')],
            'is_active' => ['sometimes', 'boolean'], // Add validation for is_active
        ]);

        // Prevent deactivation of self if admin or the only admin
        if ($user->id === Auth::id() && !$request->boolean('is_active')) {
            return redirect()->back()->with('error', 'You cannot deactivate your own account.');
        }
        if ($user->isAdmin() && User::where('role', 'admin')->where('is_active', true)->count() === 1 && !$request->boolean('is_active')) {
            return redirect()->back()->with('error', 'Cannot deactivate the only active administrator.');
        }
        if ($user->id === Auth::id() && $user->role === 'admin' && $request->role !== 'admin' && User::where('role', 'admin')->count() <= 1) {
            return redirect()->back()->with('error', 'Cannot change the role of the only administrator.');
        }


        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->specialization = $request->role === 'agent' ? $request->specialization : null;
        $user->is_active = $request->boolean('is_active'); // Handle the boolean input

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete your own account.');
        }
        if ($user->isAdmin() && User::where('role', 'admin')->count() === 1) {
            return redirect()->route('admin.users.index')->with('error', 'Cannot delete the only administrator.');
        }
        try {
            $userName = $user->name;
            $user->delete();
            return redirect()->route('admin.users.index')->with('success', "User '{$userName}' deleted successfully.");
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route('admin.users.index')->with('error', "Could not delete user '{$user->name}'. They may have associated records.");
        }
    }
}
