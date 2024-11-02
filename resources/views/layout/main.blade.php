<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('titulo')</title>
        <!--Fonte do google-->
        <link href="https://fonts.googleapis.com/css2?family=Roboto" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/sass/app.scss', 'resources/js/app.js'])
    </head>
    <body>
        <header class="mb-4">
            @livewire('navigation-menu')
            @if (View::hasSection('titulo'))
                <div class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                            @yield('titulo')
                        </h2>
                    </div>
                </div>
            @endif
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
        <strong>ICS &copy; 2024</strong>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    </body>
</html>