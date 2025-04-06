<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a list of users with filtering and search.
     */
    public function index(Request $request)
    {

        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $query = User::query();

        // Search by name or username
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%$search%")
                  ->orWhere('username', 'LIKE', "%$search%");
        }

        // Filter by role (Admin, Staff, etc.)
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->paginate(10); // Paginate results

        return response()->json($users);
    }

    /**
     * Update a user’s information (name, username, role).
     */
    public function update(Request $request, $id)
    {

        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $user = User::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|username|unique:users,username,' . $id,
            'role' => 'required|string'
        ]);

        $user->update($request->all());

        return response()->json(['message' => 'User updated successfully', 'user' => $user]);
    }

    /**
     * Activate or Deactivate a user.
     */
    public function toggleActivation(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        
        DB::table('users')
            ->where('id', $id)
            ->update(['is_active' => $user->is_active]);

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $user->is_active ? 'Activated User' : 'Deactivated User',
            'details' => 'Changed status of ' . $user->name,
        ]);

        return response()->json([
            'message' => 'User status updated successfully.',
            'is_active' => $user->is_active
        ]);
    }

    /**
     * Delete a user from the system.
     */
    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * Reset a user’s password.
     */
    public function resetPassword($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $user = User::findOrFail($id);
        $newPassword = 'password123'; // Default password

        $user->password = Hash::make($newPassword);
        $user->save();

        return response()->json([
            'message' => 'Password has been reset',
            'new_password' => $newPassword 
        ]);
    }

    //Fetch all users 
    public function getUsers()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $users = User::all();
        return response()->json($users);
    }
}
