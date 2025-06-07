@extends('layouts.app')

@section('title', 'Edit User: ' . $user->name)

@section('content')
<div class="max-w-2xl mx-auto bg-white p-6 sm:p-8 rounded-lg shadow-md">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Edit User: <span class="font-normal">{{ $user->name }}</span></h2>

    <form x-data="{ selectedRole: '{{ old('role', $user->role) }}' }" action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        {{-- ... Name, Email, Role, Specialization fields as before ... --}}

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email Address <span class="text-red-500">*</span></label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="role" class="block text-sm font-medium text-gray-700">Role <span class="text-red-500">*</span></label>
            <select name="role" id="role" x-model="selectedRole" required
                @if($user->id === Auth::id() && $user->isAdmin() && \App\Models\User::where('role', 'admin')->count() === 1) disabled @endif
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2 {{ ($user->id === Auth::id() && $user->isAdmin() && \App\Models\User::where('role', 'admin')->count() === 1) ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                @foreach($roles as $roleValue)
                <option value="{{ $roleValue }}" {{ old('role', $user->role) == $roleValue ? 'selected' : '' }}>
                    {{ ucfirst($roleValue) }}
                </option>
                @endforeach
            </select>
            @if($user->id === Auth::id() && $user->isAdmin() && \App\Models\User::where('role', 'admin')->count() === 1)
            <p class="text-xs text-gray-500 mt-1">Cannot change the role of the only administrator.</p>
            <input type="hidden" name="role" value="admin">
            @endif
            @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div x-show="selectedRole === 'agent'" x-transition>
            <label for="specialization" class="block text-sm font-medium text-gray-700">Specialization (Required for Agent) <span class="text-red-500">*</span></label>
            <select name="specialization" id="specialization" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2">
                <option value="">Select specialization...</option>
                @foreach($specializations as $spec)
                <option value="{{ $spec }}" {{ old('specialization', $user->specialization) == $spec ? 'selected' : '' }}>{{ $spec }}</option>
                @endforeach
            </select>
            @error('specialization') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- is_active checkbox --}}
        <div class="mt-4">
            <label for="is_active" class="inline-flex items-center">
                <input type="hidden" name="is_active" value="0"> {{-- Hidden input for unchecked state --}}
                <input type="checkbox" name="is_active" id="is_active" value="1"
                    {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                    @if($user->id === Auth::id() || ($user->isAdmin() && \App\Models\User::where('role', 'admin')->where('is_active', true)->count() === 1 && $user->is_active)) disabled @endif
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 h-4 w-4
                {{ ($user->id === Auth::id() || ($user->isAdmin() && \App\Models\User::where('role', 'admin')->where('is_active', true)->count() === 1 && $user->is_active)) ? 'bg-gray-200 cursor-not-allowed' : '' }}">
                <span class="ml-2 text-sm text-gray-600">User is Active</span>
                @if($user->id === Auth::id()) <p class="text-xs text-gray-500 ml-6">You cannot deactivate your own account.</p> @endif
                @if($user->isAdmin() && \App\Models\User::where('role', 'admin')->where('is_active', true)->count() === 1 && $user->is_active && $user->id !== Auth::id())
                <p class="text-xs text-gray-500 ml-6">Cannot deactivate the only active administrator.</p>
                @endif
            </label>
            @error('is_active') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <hr class="my-4">
        <p class="text-sm text-gray-600">Leave password fields blank to keep current password.</p>
        {{-- ... Password fields ... --}}
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
            <input type="password" name="password" id="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        </div>

        {{-- Buttons --}}
        <div class="flex items-center justify-end pt-3 space-x-3">
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit"
                class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300">
                Update User
            </button>
        </div>
    </form>
</div>
@endsection