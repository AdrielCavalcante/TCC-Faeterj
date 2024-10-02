<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EncryptionService;

class TestEncryptionController extends Controller
{
    protected $encryptionService;

    public function __construct(EncryptionService $encryptionService)
    {
        $this->encryptionService = $encryptionService;
    }

    public function index()
    {
        // Gerar chaves RSA
        $rsaKeys = $this->encryptionService->generateRSAKeys();
        $publicKey = $rsaKeys['public_key'];
        $privateKey = $rsaKeys['private_key'];

        // Gerar chave AES
        $aesKey = $this->encryptionService->generateAESKey();

        // Criptografar a chave AES com a chave pública RSA
        $encryptedAESKey = $this->encryptionService->encryptAESKey($aesKey, $publicKey);

        // Mensagem a ser criptografada
        $message = 'Adriel';

        // Criptografar a mensagem com AES
        $encryptedMessage = $this->encryptionService->encryptAES($message, $aesKey);

        // Descriptografar a chave AES com a chave privada RSA
        $decryptedAESKey = $this->encryptionService->decryptAESKey($encryptedAESKey, $privateKey);
    
        // Descriptografar a mensagem com AES
        $decryptedMessage = $this->encryptionService->decryptAES($encryptedMessage, $decryptedAESKey);

        return view('test-encrypt', [
            'message' => $message,
            'AESkey' => $aesKey,
            'encryptedAESKey' => $encryptedAESKey,
            'decryptedAESKey' => $decryptedAESKey,
            'encryptedMessage' => $encryptedMessage,
            'decryptedMessage' => $decryptedMessage,
            'public' => $publicKey,
            'private' => $privateKey
        ]);
    }
}
