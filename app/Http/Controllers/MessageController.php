<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\EncryptionService;
use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;

class MessageController extends Controller
{
    protected $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    public function downloadDecryptedFile($messageId)
    {
        $message = Message::findOrFail($messageId);
     
        $filePath = $message->file_path;

        // Inverte a string
        $reversedFilePath = strrev($filePath);

        // Encontra a posição do primeiro ponto na string invertida
        $firstDotPosition = strpos($reversedFilePath, '.');

        // Se encontrou um ponto, pega a substring até esse ponto (na string original)
        $extensao = strrev(substr($reversedFilePath, 0, $firstDotPosition + 1));

        if(!$filePath) {
            return response()->json(['error' => 'Arquivo não encontrado.'], 404);
        }

        $key = request()->input('key');
        $owner = request()->input('owner');

        // Supondo que você já tenha a lógica para recuperar as chaves AES e o privateKey
        if ($message->encrypted) {
            if ($owner === 'sender') {
                $aesKeyCrypted = $message->encryption_key_sender;
            } else if ($owner === 'receiver') {
                $aesKeyCrypted = $message->encryption_key_receiver;
            } else {
                return response()->json(['error' => 'Proprietário inválido.'], 400);
            }

            $fileContent = file_get_contents(storage_path('app/public/' . $filePath));

            $aesKey = $this->encryptionService->decryptAESKey($aesKeyCrypted, $key);

            $decryptedContent = $this->encryptionService->decryptAES($fileContent, $aesKey);
            
            // Salvar o conteúdo descriptografado em um arquivo temporário
            $tempFileName = 'decrypted_' . uniqid() . $extensao; // Defina a extensão correta
            $tempFilePath = storage_path('app/public/temp/' . $tempFileName);

            file_put_contents($tempFilePath, $decryptedContent);

            // Retornar uma resposta para download
            return response()->download($tempFilePath)->deleteFileAfterSend(true);
        }

        return response()->json(['error' => 'Mensagem não encontrada ou não criptografada.'], 404);
    }

    public function showChat($receiverId)
    {
        $user = Auth::user(); // ID do usuário atual logado
        $userId = $user->id;

        $receiver = User::findOrFail($receiverId);

        return view('dashboard/chat', [
            'user' => $user,
            'receiver' => $receiver
        ]);
    }

    public function getMessages($receiverId)
    {
        $userId = Auth::id(); // ID do usuário atual logado
        
        $privateKey = request()->input('key');

        // Recupera todas as mensagens entre o usuário logado e o receptor
        $messages = Message::where(function($query) use ($userId, $receiverId) {
            $query->where('sender_id', $userId)->where('receiver_id', $receiverId);
        })->orWhere(function($query) use ($userId, $receiverId) {
            $query->where('sender_id', $receiverId)->where('receiver_id', $userId);
        })->orderBy('created_at', 'desc') // Ordena por data para pegar a última mensagem
        ->get();

        if ($messages->isNotEmpty()) {
            $lastReceivedMessage = $messages->firstWhere('receiver_id', $userId); // Última mensagem recebida pelo usuário logado
            
            // Se houver uma mensagem recebida do outro usuário e não estiver lida, marca como lida
            if ($lastReceivedMessage && $lastReceivedMessage->sender_id != $userId && !$lastReceivedMessage->read) {
                $lastReceivedMessage->read = 1;
                $lastReceivedMessage->save();
            }
        }
        
        foreach($messages as $message) {
            if($message->encrypted) {
                try {
                    if ($message->receiver_id === $userId) {
                        $aesKey = $this->encryptionService->decryptAESKey($message->encryption_key_receiver, $privateKey);
                        if(!empty($message->content)) {
                            $decryptedContent = $this->encryptionService->decryptAES($message->content, $aesKey);
                            $message->content = $decryptedContent;
                        }

                    } else if ($message->sender_id === $userId) {
                        $aesKey = $this->encryptionService->decryptAESKey($message->encryption_key_sender, $privateKey);
                        if(!empty($message->content)) {
                            $decryptedContent = $this->encryptionService->decryptAES($message->content, $aesKey);
                            $message->content = $decryptedContent;
                        }
                    }
                } catch (RuntimeException $e) {
                    // Log o erro para referência futura
                    error_log("Erro ao descriptografar a mensagem: " . $e->getMessage());
                    
                    // Envia uma resposta personalizada
                    return response()->json(['error' => 'Falha ao descriptografar a mensagem. Verifique suas chaves.'], 400);
                }
            }
        }


        return response()->json($messages);
    }

    public function send(Request $request)
    {
        // Iniciar o tempo
        $startTime = microtime(true);

        // Forçar a resposta como JSON
        $request->headers->set('Accept', 'application/json');

        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'file' => 'nullable|file|mimes:txt,svg,jpg,png,pdf,docx,zip,xlsx,xls,doc|max:40960', // 40 MB em KB
            'content' => 'nullable|string',
            'cipher' => 'nullable|boolean'
        ]);

        // Verifica se pelo menos um dos dois foi enviado
        if (!$request->hasFile('file') && !$request->filled('content')) {
            return response()->json(['error' => 'Você precisa preencher algum conteúdo'], 400);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $receiver = User::find($request->receiver_id);
        if (!$receiver) {
            return response()->json(['error' => 'Receiver not found'], 404);
        }

        $cipher = $request->input('cipher');
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            
            $fileSize = $file->getSize();
            
            $maxStorageLimit = 200 * 1024 * 1024; // 200 MB em bytes
            
            // Verificar se o tamanho usado + o novo arquivo não ultrapassa o limite
            if (($user->storage_used + $fileSize) > $maxStorageLimit) {
                return response()->json(['error' => 'Limite de armazenamento excedido.'], 400);   
            }

            $filePath = $file->store('messages', 'public');
            $user->storage_used += $fileSize;
            $user->save();

            // Criptografar o conteúdo do arquivo
            $fileContent = file_get_contents(storage_path('app/public/' . $filePath));
            $aesKey = $this->encryptionService->generateAESKey();
            $keySender = $this->encryptionService->encryptAESKey($aesKey, $user->public_key);
            $keyReceiver = $this->encryptionService->encryptAESKey($aesKey, $receiver->public_key);

            $encryptedContent = $this->encryptionService->encryptAES($fileContent, $aesKey);

            // Substituir o conteúdo do arquivo com o conteúdo criptografado
            file_put_contents(storage_path('app/public/' . $filePath), $encryptedContent);

            $message = Message::create([
                'sender_id' => $user->id,
                'receiver_id' => $receiver->id,
                'file_path' => $filePath,
                'content' => '',
                'encrypted' => $cipher,
                'encryption_key_sender' => $keySender,
                'encryption_key_receiver' => $keyReceiver,
            ]);

        } else {
            // Caso tenha apenas conteúdo de texto
            $content = $request->input('content');

            if (!$receiver->public_key) {
                return response()->json(['error' => 'Receiver does not have a public key'], 400);
            }
            
            if($cipher) {
                $aesKey = $this->encryptionService->generateAESKey();
                $keySender = $this->encryptionService->encryptAESKey($aesKey, $user->public_key);
                $keyReceiver = $this->encryptionService->encryptAESKey($aesKey, $receiver->public_key);
    
                $encryptedContent = $this->encryptionService->encryptAES($content, $aesKey);
                
                $message = Message::create([
                    'sender_id' => $user->id,
                    'receiver_id' => $receiver->id,
                    'content' => $encryptedContent,
                    'encrypted' => true,
                    'encryption_key_sender' => $keySender,
                    'encryption_key_receiver' => $keyReceiver,
                ]);
            } else {
                $message = Message::create([
                    'sender_id' => $user->id,
                    'receiver_id' => $receiver->id,
                    'content' => $content,
                    'encrypted' => false,
                    'encryption_key_sender' => '',
                    'encryption_key_receiver' => '',
                ]);
                
            }

            if($user && $receiver) {
                broadcast(new MessageSent($message, $content, $user, $receiver))->toOthers();
            }
        }

        // Finalizar o tempo e calcular a diferença
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        if($cipher) {
            Log::channel('velocidade')->info('Tempo de execução da mensagem: ' . $executionTime . ' segundos. (Criptografado)');
        } else {
            Log::channel('velocidade')->info('Tempo de execução da mensagem: ' . $executionTime . ' segundos. (Texto claro)');
        }

        return response()->json(['message' => 'Message sent successfully', 'status' => 201], 201);
    }
}