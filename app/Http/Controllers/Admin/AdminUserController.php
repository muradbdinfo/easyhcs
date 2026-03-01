<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\System\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/AdminUsers/Index', [
            'users' => AdminUser::latest()->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:admin_users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        AdminUser::create([
            'name'           => $data['name'],
            'email'          => $data['email'],
            'password'       => Hash::make($data['password']),
            'is_super_admin' => false,
        ]);

        return back()->with('success', 'Admin user created.');
    }

    public function update(Request $request, AdminUser $user)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => "required|email|unique:admin_users,email,{$user->id}",
            'password' => 'nullable|min:8|confirmed',
        ]);

        $user->update([
            'name'  => $data['name'],
            'email' => $data['email'],
            ...(isset($data['password']) ? ['password' => Hash::make($data['password'])] : []),
        ]);

        return back()->with('success', 'Admin user updated.');
    }

    public function destroy(AdminUser $user)
    {
        if ($user->is_super_admin) {
            return back()->with('error', 'Cannot delete the super admin.');
        }
        $user->delete();
        return back()->with('success', 'Admin user deleted.');
    }
}