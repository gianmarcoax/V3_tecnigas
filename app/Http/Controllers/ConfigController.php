<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ConfigController extends Controller
{
    private const ROLES = ['admin', 'administrador', 'almacen', 'vendedor', 'limpieza'];

    public function index()
    {
        $users = User::orderBy('role')->orderBy('name')->get();
        $roles = self::ROLES;
        $roleStats = [
            'admin'         => $users->where('role', 'admin')->count(),
            'administrador' => $users->where('role', 'administrador')->count(),
            'almacen'       => $users->where('role', 'almacen')->count(),
            'vendedor'      => $users->where('role', 'vendedor')->count(),
            'limpieza'      => $users->where('role', 'limpieza')->count(),
        ];
        return view('config.index', compact('users', 'roles', 'roleStats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'role'     => 'required|in:' . implode(',', self::ROLES),
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'name.required'     => 'El nombre es obligatorio.',
            'email.required'    => 'El correo es obligatorio.',
            'email.unique'      => 'Este correo ya está registrado.',
            'role.required'     => 'Debes seleccionar un rol.',
            'role.in'           => 'El rol seleccionado no es válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed'=> 'Las contraseñas no coinciden.',
            'password.min'      => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'role'     => $request->role,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('config.index')->with('success', "Usuario '{$request->name}' creado exitosamente.");
    }

    public function update(Request $request, User $user)
    {
        $rules = [
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role'  => 'required|in:' . implode(',', self::ROLES),
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['confirmed', Password::min(8)];
        }

        $request->validate($rules, [
            'name.required'      => 'El nombre es obligatorio.',
            'email.required'     => 'El correo es obligatorio.',
            'email.unique'       => 'Este correo ya está registrado.',
            'role.required'      => 'Debes seleccionar un rol.',
            'role.in'            => 'El rol seleccionado no es válido.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
            'role'  => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('config.index')->with('success', "Usuario '{$user->name}' actualizado correctamente.");
    }

    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->route('config.index')->with('error', 'No puedes eliminar tu propio usuario.');
        }

        // Ensure there's always at least one admin
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('config.index')->with('error', 'No puedes eliminar el único administrador del sistema.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('config.index')->with('success', "Usuario '{$name}' eliminado correctamente.");
    }
}
