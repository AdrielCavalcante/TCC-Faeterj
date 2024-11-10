<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\EncryptionService;
use Illuminate\Support\Facades\Auth;

class GenerateKeys extends Component
{
    public function generateKeys()
    {
        // Pega o usuário autenticado	
        $user = Auth::user();

        // Remove todas mensagens daquele usuário
        $user->sentMessages()->delete();
        $user->receivedMessages()->delete();

        // Gera as chaves RSA
        $keys = EncryptionService::generateRSAKeys();

        // Atualiza o usuário com a nova chave pública
        $user->update([
            'public_key' => $keys['public_key'],
        ]);

        // Armazena a chave privada na sessão
        session(['private_key' => $keys['private_key']]);

        // Redireciona para o dashboard
        return redirect()->to('/dashboard');
    }

    public function render()
    {
        return view('livewire.generate-keys');
    }
}
