<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get(['id', 'name', 'email', 'role', 'sales_man']);

        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:admin,user'],
            'sales_man' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $password = $data['password'] ?? Str::password(14);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'sales_man' => $data['sales_man'] ?? null,
            'password' => Hash::make($password),
        ]);

        Activity::log("Team account <b>{$user->name}</b> created", 'bi-person-plus-fill', 'success');

        return redirect()->route('users.index')->with(
            'success',
            "Account created for {$user->email}.".(($data['password'] ?? null) ? '' : " Temporary password: {$password} (share this securely — it will not be shown again).")
        );
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:admin,user'],
            'sales_man' => ['nullable', 'string', 'max:255'],
        ]);

        if ($user->id === auth()->id() && $data['role'] !== User::ROLE_ADMIN) {
            return redirect()->route('users.index')->with('error', "You can't remove your own admin access.");
        }

        $user->update($data);

        Activity::log("Team account <b>{$user->name}</b> updated", 'bi-pencil-fill', 'primary');

        return redirect()->route('users.index')->with('success', 'Account updated.');
    }

    public function resetPassword(User $user)
    {
        $password = Str::password(14);
        $user->update(['password' => Hash::make($password)]);

        Activity::log("Password reset for <b>{$user->name}</b>", 'bi-key-fill', 'warning');

        return redirect()->route('users.index')->with('success', "New temporary password for {$user->email}: {$password} (share this securely — it will not be shown again).");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', "You can't delete your own account.");
        }

        $name = $user->name;
        $user->delete();

        Activity::log("Team account <b>{$name}</b> removed", 'bi-trash-fill', 'danger');

        return redirect()->route('users.index')->with('success', 'Account removed.');
    }
}
