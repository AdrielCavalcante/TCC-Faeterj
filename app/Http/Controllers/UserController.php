<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PrivateKey;
use App\Services\EncryptionService;

class UserController extends Controller
{
    protected $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
        ]);

        $keys = $this->encryptionService->generateKeys();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'public_key' => $keys['public_key']
        ]);

        PrivateKey::create([
            'user_id' => $user->id,
            'private_key' => $keys['private_key']
        ]);

        return response()->json(['status' => 'User registered successfully']);
    }

    // Lista os usuários registrados
    public function dashboard() {
        $usuarios = User::all();

        if (count($usuarios) === 0) {
            $usuarios = 'Nenhum usuário registrado';
        }

        $currentUserId = auth()->id();

        return view('dashboard', [
            'usuarios' => $usuarios,
            'currentUserId' => $currentUserId
        ]);
    }
}
