<?php

namespace App\Services;

use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\AES;
use Illuminate\Support\Facades\Crypt;

class EncryptionService
{
    private $rsa;
    private $aes;

    public function __construct()
    {
        // Crie uma instância do RSA para a criptografia de chaves
        $this->rsa = RSA::createKey(2048);
    }

    // Criptografa a chave AES com a chave pública RSA
    public function encryptAESKey($aesKey, $publicKey)
    {
        $rsaPublicKey = RSA::load($publicKey);
        return base64_encode($rsaPublicKey->encrypt($aesKey));
    }
    
    // Descriptografa a chave AES com a chave privada RSA
    public function decryptAESKey($encryptedKey, $privateKey)
    {
        $rsaPrivateKey = RSA::load($privateKey);
        return $rsaPrivateKey->decrypt(base64_decode($encryptedKey));
    }

    // Criptografa uma mensagem com AES-256-GCM
    public function encryptAES($message, $aesKey)
    {
        $aes = new AES('gcm');
        $aes->setKey($aesKey);

        $nonce = random_bytes(12);
        $aes->setNonce($nonce);
        
        $cipherText = $aes->encrypt($message);
        
        $tag = $aes->getTag();

        return base64_encode($nonce . $tag . $cipherText);
    }

    // Descriptografa uma mensagem com AES-256-GCM
    public function decryptAES($cipherText, $aesKey)
    {
        $aes = new AES('gcm');
        $aes->setKey($aesKey);
        $cipherText = base64_decode($cipherText);
        
        $nonce = substr($cipherText, 0, 12);
        $tag = substr($cipherText, 12, 16);
        $cipherText = substr($cipherText, 28);
        
        $aes->setNonce($nonce);
        $aes->setTag($tag); 
        
        $message = $aes->decrypt($cipherText);

        return $message;
    }

    // Gera um par de chaves RSA
    public static function generateRSAKeys()
    {
        $keyPair = RSA::createKey(2048);
        return [
            'public_key' => $keyPair->getPublicKey(),
            'private_key' => $keyPair
        ];
    }

    // Gera uma chave AES-256
    public function generateAESKey()
    {
        return random_bytes(32); // 256 bits
    }
}
