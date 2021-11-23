const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .scripts(['resources/js/jquery.bpopup.min.js'], 'public/js/jquery.bpopup.min.js')
    .scripts(['resources/js/jquery.datetimepicker.full.min.js'], 'public/js/jquery.datetimepicker.full.min.js')
    .scripts(['resources/js/moment.min.js'], 'public/js/moment.min.js')
    .scripts(['resources/js/sweetalert2.js'], 'public/js/sweetalert2.js')
    .sass('resources/sass/app.scss', 'public/css')
    .version();

