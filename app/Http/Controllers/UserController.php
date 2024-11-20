<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;
use App\Services\EncryptionService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    protected $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    // Lista os usuários registrados
    public function dashboard() {
        $user = auth()->user();
        $currentUserId = $user->id;
        
        if ($user->hasRole('admin')) {
            $usuarios = User::all();
            $mensagens = Message::where('content', '!=', '')->get();
            $mensagensArquivos = Message::where('file_path', '!=', '')->get();
            return view('dashboard.admin', compact('usuarios', 'mensagens', 'mensagensArquivos', 'currentUserId')); // View para administradores
        } else {
            // Buscar usuários com as mensagens enviadas ou recebidas, ordenando pelas mensagens mais recentes
            $usuarios = User::whereHas('sentMessages', function ($query) use ($currentUserId) {
                $query->where('receiver_id', $currentUserId);
            })
            ->orWhereHas('receivedMessages', function ($query) use ($currentUserId) {
                $query->where('sender_id', $currentUserId);
            })
            ->with([
                'sentMessages' => function ($query) use ($currentUserId) {
                    $query->where('receiver_id', $currentUserId)
                        ->latest() // Ordena pela data mais recente
                        ->limit(1); // Pega apenas a mensagem mais recente
                },
                'receivedMessages' => function ($query) use ($currentUserId) {
                    $query->where('sender_id', $currentUserId)
                        ->latest() // Ordena pela data mais recente
                        ->limit(1); // Pega apenas a mensagem mais recente
                }
            ])
            ->get();
        }

        if (request()->has('removeKey')) {
            session()->forget('private_key'); // Remove a chave privada da sessão
        }

        $privateKey = session()->get('private_key', null); // Retorna nulo se não tiver 

        // Usando compact para passar as variáveis para a view
        return view('dashboard.index', compact('usuarios', 'currentUserId', 'privateKey'));
    }

    public function listUsers() {
        $user = auth()->user();
        $currentUserId = $user->id;
        $usuarios = User::all();

        // Usando compact para passar as variáveis para a view
        return view('dashboard.list', compact('usuarios', 'currentUserId'));
    }

    public function update(Request $request, $id)
    {
        // Validação dos dados
        $request->validate([
            'nome' => 'required|string|max:255',
            'email' => [
                'required', 
                'email', 
                'unique:users,email,' . $id,
                'regex:/^.+@faeterj-rio\.edu\.br$/i', // Regex para garantir o domínio
            ],
            'setor' => 'nullable|string|max:255',
            'senha' => 'nullable|string|min:8',
        ], [
            'email.regex' => 'O e-mail deve pertencer ao domínio @faeterj-rio.edu.br.', // Mensagem personalizada
        ]);

        // Encontre o usuário pelo ID
        $user = User::findOrFail($id);

        // Atualiza os dados
        $user->name = $request->input('nome');
        $user->email = $request->input('email');
        $user->sector = $request->input('setor');

        // Atualiza a senha se fornecida
        if ($request->filled('senha')) {
            $user->password = Hash::make($request->input('senha'));
        }

        // Salva as mudanças
        $user->save();

        // Redireciona de volta com uma mensagem de sucesso
        return redirect()->route('dashboard')->with('success', 'Usuário atualizado com sucesso!');
    }

    public function delete(Request $request, $id) {
        $user = User::findOrFail($id);

        if(!$user) {
            return response()->json(['error' => 'Usuário não encontrado'], 404);
        }

        // Pega todas mensagens do usuário e recebidas, para remover
        $sendMessagesArquivos = $user->sentMessages()->where('file_path', '!=', '')->get();
        $receivedMessagesArquivos = $user->receivedMessages()->where('file_path', '!=', '')->get();

        // Para cada mensagem com arquivo, remova o arquivo do storage
        foreach ($sendMessagesArquivos as $message) {
            // Verifica se o caminho do arquivo existe
            if (Storage::disk('public')->exists($message->file_path)) {
                // Exclui o arquivo do storage público
                Storage::disk('public')->delete($message->file_path);
            }
        }

        foreach ($receivedMessagesArquivos as $message) {
            // Verifica se o caminho do arquivo existe
            if (Storage::disk('public')->exists($message->file_path)) {
                // Exclui o arquivo do storage público
                Storage::disk('public')->delete($message->file_path);
            }
        }

        $user->sentMessages()->delete(); // Deleta todas as mensagens enviadas
        $user->receivedMessages()->delete(); // Deleta todas as mensagens recebidas

        $user->delete(); // Deleta o usuário

        return redirect()->route('dashboard')->with('success', 'Usuário removido com sucesso');
    }

    public function removerArquivos(Request $request, $id)
    {
        // Encontre o usuário pelo ID
        $user = User::findOrFail($id);

        // Pega todas mensagens arquivos do usuário e remove
        $messages = $user->sentMessages()->where('file_path', '!=', '')->get();

        // Para cada mensagem com arquivo, remova o arquivo do storage e depois exclua o registro
        foreach ($messages as $message) {
            // Verifica se o caminho do arquivo existe
            if (Storage::disk('public')->exists($message->file_path)) {
                // Exclui o arquivo do storage público
                Storage::disk('public')->delete($message->file_path);
            }
            // Deleta a mensagem
            $message->delete();
        }

        $user->storage_used = 0; // Zera o espaço usado

        // Salva as mudanças
        $user->save();

        // Redireciona de volta com uma mensagem de sucesso
        return redirect()->route('dashboard')->with('success', 'Arquivos removidos com sucesso!');
    }
}
