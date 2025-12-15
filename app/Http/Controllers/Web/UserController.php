<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of all users.
     */
    public function index(Request $request)
    {
        // Only admins can access
        if (auth()->user()->role !== 'admin') {
            abort(403, 'You do not have permission to manage users.');
        }

        $users = User::orderBy('created_at', 'desc')->paginate(15);
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for editing a user's role.
     */
    public function edit(User $user)
    {
        // Only admins can access
        if (auth()->user()->role !== 'admin') {
            abort(403, 'You do not have permission to manage users.');
        }

        return view('users.edit', compact('user'));
    }

    /**
     * Update the user's role.
     */
    public function update(Request $request, User $user)
    {
        // Only admins can access
        if (auth()->user()->role !== 'admin') {
            abort(403, 'You do not have permission to manage users.');
        }

        // Prevent self-demotion from admin
        if ($user->id === auth()->id() && $request->role !== 'admin') {
            return back()->with('error', 'You cannot demote yourself from admin role.');
        }

        $data = $request->validate([
            'role' => 'required|in:admin,manager,viewer',
        ]);

        $oldRole = $user->role;
        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', "User '{$user->name}' role changed from {$oldRole} to {$data['role']}.");
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user)
    {
        // Only admins can access
        if (auth()->user()->role !== 'admin') {
            abort(403, 'You do not have permission to manage users.');
        }

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "User '{$name}' has been deleted.");
    }
}


