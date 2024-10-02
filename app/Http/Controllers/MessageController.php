<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Services\EncryptionService;
use Illuminate\Support\Facades\Auth;
use App\Models\PrivateKey;
use App\Models\User;

class MessageController extends Controller
{
    protected $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    public function showChat()
    {
        $userId = Auth::id(); // ID do usuário atual logado
        $receiverId = 2; // ID do usuário com quem o usuário atual está conversando
        
        $privateKey = PrivateKey::where('user_id', $userId)->first()->private_key;

        $messages = Message::where(function($query) use ($userId, $receiverId) {
            $query->where('sender_id', $userId)->where('receiver_id', $receiverId);
        })->orWhere(function($query) use ($userId, $receiverId) {
            $query->where('sender_id', $receiverId)->where('receiver_id', $userId);
        })->get();

        foreach($messages as $message) {
            if (($message->encrypted) && ($message->receiver_id === $userId)) {
                $aesKey = $this->encryptionService->decryptAESKey($message->encryption_key_receiver, $privateKey);
                $decryptedContent = $this->encryptionService->decryptAES($message->content, $aesKey);
                $message->content = $decryptedContent;

            } else if (($message->encrypted) && ($message->sender_id === $userId)) {
                $aesKey = $this->encryptionService->decryptAESKey($message->encryption_key_sender, $privateKey);
                $decryptedContent = $this->encryptionService->decryptAES($message->content, $aesKey);
                $message->content = $decryptedContent;
            }
        }


        return view('messages', [
            'messages' => $messages,
            'userId' => $userId,
            'receiverId' => $receiverId
        ]);
    }

    public function send(Request $request)
    {
        // Forçar a resposta como JSON
        $request->headers->set('Accept', 'application/json');

        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'file' => 'nullable|file|mimes:jpg,png,pdf,docx,zip|max:2048', // Restrição de tipo e tamanho
            'content' => 'nullable|string', // Validação opcional para conteúdo de texto
        ]);

        // Verifica se pelo menos um dos dois foi enviado
        if (!$request->hasFile('file') && !$request->filled('content')) {
            return response()->json(['error' => 'You must provide either a file or content'], 400);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $receiver = User::find($request->receiver_id);
        if (!$receiver) {
            return response()->json(['error' => 'Receiver not found'], 404);
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filePath = $file->store('messages', 'public');

            // Criptografar o conteúdo do arquivo
            $fileContent = file_get_contents(storage_path('app/public/' . $filePath));
            $aesKey = 'EssaChaveImpossivelDeAdivinharOk';
            $chave = $this->encryptionService->encryptAESKey($aesKey, $receiver->public_key);

            $encryptedContent = $this->encryptionService->encryptAES($fileContent, $aesKey);

            Message::create([
                'sender_id' => $user->id,
                'receiver_id' => $receiver->id,
                'file_path' => $filePath,
                'content' => $encryptedContent,
                'encrypted' => true,
                'encryption_key' => $chave,
            ]);
        } else {
            // Caso tenha apenas conteúdo de texto
            $content = $request->input('content');
            if (!$receiver->public_key) {
                return response()->json(['error' => 'Receiver does not have a public key'], 400);
            }

            $aesKey = 'EssaChaveImpossivelDeAdivinharOk';
            $keySender = $this->encryptionService->encryptAESKey($aesKey, $user->public_key);
            $keyReceiver = $this->encryptionService->encryptAESKey($aesKey, $receiver->public_key);

            $encryptedContent = $this->encryptionService->encryptAES($content, $aesKey);

            Message::create([
                'sender_id' => $user->id,
                'receiver_id' => $receiver->id,
                'content' => $encryptedContent,
                'encrypted' => true,
                'encryption_key_sender' => $keySender,
                'encryption_key_receiver' => $keyReceiver,
            ]);
        }

        return response()->json(['message' => 'Message sent successfully', 'status' => 201]);
    }

    public function receive($messageId)
    {
        $message = Message::findOrFail($messageId);
        $user = Auth::user();

        if ($message->receiver_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($message->file_path) {
            $filePath = storage_path('app/public/' . $message->file_path);
            if (!file_exists($filePath)) {
                return response()->json(['error' => 'File not found'], 404);
            }

            // Descriptografar o conteúdo do arquivo
            $privateKey = $user->privateKey->private_key;
            $encryptedContent = $message->content;
            $decryptedContent = $this->encryptionService->decryptMessage($encryptedContent, $privateKey);

            file_put_contents($filePath, $decryptedContent);
            return response()->download($filePath);
        } else {
            if (!$user->privateKey) {
                return response()->json(['error' => 'User does not have a private key'], 400);
            }

            $privateKey = $user->privateKey->private_key;
            $decryptedContent = $this->encryptionService->decryptMessage($message->content, $privateKey);
            return response()->json(['content' => $decryptedContent]);
        }
    }
}