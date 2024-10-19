<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('titulo')</title>
        <!--Fonte do google-->
        <link href="https://fonts.googleapis.com/css2?family=Roboto" rel="stylesheet">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <header>
            <nav class="navbar navbar-expand-lg navbar-light">
                <a href="/" class="navbar-brand">
                    <img src="/img/logo.svg" alt="logo">
                </a>    
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbar">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <a href="/" class="nav-link">Eventos</a>
                        </li>
                        <li class="nav-item">
                            <a href="../eventos/create" class="nav-link">Criar Eventos</a>
                        </li>
                        @auth
                        <li class="nav-item">
                            <a href="/dashboard" class="nav-link">Meus eventos</a>
                        </li>      
                        <li class="nav-item">
                            <form action="/logout" method="post">
                                @csrf
                                <a href="/logout" class="nav-link" onclick="event.preventDefault();
                                this.closest('form').submit();">
                                Sair da Conta
                                </a>
                            </form>
                        </li>                     
                        @endauth
                        @guest
                        <li class="nav-item">
                            <a href="/login" class="nav-link">Entrar</a>
                        </li>
                        <li class="nav-item">
                            <a href="/register" class="nav-link">Cadastrar</a>
                        </li>
                        @endguest
                    </ul>
                </div>
            </nav>
        </header>
        <main>
            <div class="container-fluid">
                <div class="row">
                @if(session('msg'))
                    <p class="msg">{{session('msg')}}</p>
                @endif
                @yield('content')
                </div>
            </div>
        </main>
    <footer>
        <p>ICS &copy; 2024</p>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    </body>
</html>