<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('titulo')</title>
        <!--Fonte do google-->
        <link href="https://fonts.googleapis.com/css2?family=Roboto" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        @vite(['resources/css/app.css', 'resources/sass/app.scss', 'resources/js/app.js'])
    </head>
    <body>
        <header class="mb-4">
            @livewire('navigation-menu')
            @if (View::hasSection('titulo'))
                <div class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <div class="d-flex gap-3 align-items-center">
                            @if(View::hasSection('imagemTitulo'))
                                <img src="@yield('imagemTitulo')" alt="Imagem do título" class="w-10 h-10 rounded-full object-cover">
                            @endif
                            <h2 class="font-semibold mb-0 text-xl text-gray-800 leading-tight">
                                @yield('titulo')
                            </h2>
                        </div>
                    </div>
                </div>
            @endif
        </header>
        @if(View::hasSection('chat'))
            <main class="chat-main">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="row">
                    @if(session('msg'))
                        <p class="msg">{{session('msg')}}</p>
                    @endif
                    @yield('content')
                    </div>
                </div>
            </main>
        @else
            <main>
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="row">
                    @if(session('msg'))
                        <p class="msg">{{session('msg')}}</p>
                    @endif
                    @yield('content')
                    </div>
                </div>
            </main>
        @endif
    <footer>
        <strong>ICS &copy; 2024</strong>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    </body>
</html>