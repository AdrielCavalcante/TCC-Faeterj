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
        \Log::channel('auditoria')->info($usuarios[0]->audits);

        if (count($usuarios) === 0) {
            $usuarios = 'Nenhum usuário registrado';
        }

        $currentUserId = auth()->id();
        
        if (request()->has('removeKey')) {
            session()->forget('private_key'); // Remove a chave privada da sessão
        }

        $privateKey = session()->get('private_key', null); // Retorna nulo se não tiver 

        // Usando compact para passar as variáveis para a view
        return view('dashboard.index', compact('usuarios', 'currentUserId', 'privateKey'));
    }
}
