<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function login(Request $request)
    {
        // Validar os dados da requisição
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Extrair as credenciais da requisição
        $credentials = $request->only('email', 'password');

        // Verificar se o usuário existe e a senha está correta
        if (Auth::attempt($credentials)) {
            // Usuário autenticado com sucesso
            $user = Auth::user();
            // Criar um token para o usuário (opcional, se estiver usando tokens)
            $token = $user->createToken('Personal Access Token')->plainTextToken;

            return response()->json(['token' => $token], 200);
        }

        // Falha na autenticação
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        return response()->json(['message' => 'Logout successful'], 200);
    }
}
