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
    <div id="app" v-cloak>
        <p v-html="message"></p>
        <div v-html="text1"></div>
    </div>
    
    @vite('resources/js/app.js')
</body>
</html>
