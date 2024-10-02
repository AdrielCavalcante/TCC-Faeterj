const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js')
   .vue() // Habilita o suporte a Vue.js
   .sass('resources/sass/app.scss', 'public/css')
   .options({
       processCssUrls: false // Para evitar problemas com URLs em arquivos CSS
   });
