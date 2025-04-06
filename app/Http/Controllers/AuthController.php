<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register (Request $request)
    {   
        //Validate
        $fields = $request->validate([  
            'avatar' => ['file', 'nullable', 'max:5074'],
            'role'=>['required', 'max:255'],
            'name' => ['required', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'min:8', 'confirmed'],   
        ]);

        if ($request->hasFile('avatar')){
           $fields['avatar'] = Storage::disk('public')->put('avatars', $request->avatar);
        }


        //Register
        $user = User::create([
            'name' => $fields['name'],
            'username' => $fields['username'],
            'password' => Hash::make($fields['password']),
            'role' => $fields['role'],
            'avatar' => $fields['avatar'] ?? null,

            // additional field for admin approval
            'is_approved' => false, 
        ]);
        
        // Add flash message for pending approval
        return redirect()->route('login')->with('success', 'Your account is pending waiting for approval by the admin.');

    }


    //Login function
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $user = User::where('username', $credentials['username'])->first();

        //Check if user exists  in the database
        if (!$user) {
            return back()->withErrors([
                'username' => 'Username does not exist.',
            ])->onlyInput('username');
        }

        //Check if password is correct
        if (!Auth::attempt($credentials, $request->remember)) {
            return back()->withErrors([
                'password' => 'Incorrect password.',
            ])->onlyInput('password');
        }

        //Check if user is approved
        if (!$user->is_approved) {
            Auth::logout();
            return back()->withErrors([
                'username' => 'Your account is pending approval by the admin.',
            ]);
        }

        //Check if user is active
        // if (!$user->is_active) { 
        //     Auth::logout();
        //     return redirect()->route('login')->withErrors([
        //         'username' => 'Your account is inactive. Please contact the admin to reactivate your account.',
        //     ]);
        // }
        
        return redirect()->intended('/dashboard');
    }

    //Logout
    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    
    //Fetch pending users
    public function getPendingUsers()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }
        
        $users = User::where('is_approved', false)->get();
        return response()->json($users);
    }

    //Function to approve users
    public function approveUser($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $user = User::findOrFail($id);
        $user->is_approved = true;
        $user->save();

        return back()->with('success', 'User approved successfully.');
    }

    //Function reject approval of users
    public function rejectUser($id)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $user = User::findOrFail($id);
        $user->delete();

        return back()->with('success', 'User rejected successfully.');
    }

    //retrive the users
    public function retrieve()
    {
        $users = User::all();
        return response()->json($users);
    }
}
