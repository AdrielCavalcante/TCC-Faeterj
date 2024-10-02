<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vue Example</title>
    @vite('resources/sass/app.scss')
</head>
<body>
    <div>
        <p><strong>Original message:</strong> {{ $message }}</p>
        <p><strong>Encrypted message (AES):</strong> {{ $encryptedMessage }}</p>
        <p><strong>Decrypted message (AES):</strong> {{ $decryptedMessage }}</p>
        <p><strong>AES key:</strong> {{ $AESkey }}</p>
        <p><strong>Encrypted AES key (RSA):</strong> {{ $encryptedAESKey }}</p>
        <p><strong>Decrypted AES key (RSA):</strong> {{ $decryptedAESKey }}</p>
        <p><strong>Public: </strong> {{ $public }}</p>
        <p><strong>Private: </strong> {{ $private }}</p>
    </div>

    @vite('resources/js/app.js')
</body>
</html>
