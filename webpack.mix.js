const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management (solo JavaScript)
 |--------------------------------------------------------------------------
 |
 | El CSS ya viene compilado en public/css (app.css y auth.css). Este build
 | recompila unicamente los bundles JS porque el SCSS original depende de
 | fuentes que no estan incluidas en el repositorio y de node-sass, que no
 | soporta Node 18+.
 |
 | Uso:  npm run assets
 |
 | El script hace tres cosas:
 |   1. Crea un shim node-sass -> sass (dart-sass).
 |   2. Ejecuta webpack con --openssl-legacy-provider.
 |   3. Vuelve a agregar las entradas CSS en public/mix-manifest.json.
 |
 | No se generan source maps para evitar bundles de decenas de MB.
 |
 */

mix.js('resources/js/app.js', 'public/js')
   .extract(['vue'])
   .version();

/*
 | Desactivar la compresion de uglify (se conserva el mangling)
 |--------------------------------------------------------------------------
 | El minificador que trae Laravel Mix 3 (uglify-es 3.3.x) tiene un bug al
 | comprimir Vue 2.7: "inlines" el cuerpo de `patchVnode` dentro de `patch()`
 | y reutiliza el nombre manglado de una constante local (`isRealElement`)
 | en lugar de `isUndef`. En tiempo de ejecucion eso lanza
 | "s is not a function" al actualizar el DOM (por ejemplo, al ocultar los
 | skeletons del dashboard) y deja la pagina a medio pintar.
 |
 | Desactivar `compress` evita esa transformacion; `mangle` sigue activo para
 | mantener los bundles pequenos. Probado con Vue 2.7.16 + Element UI 2.13.2.
 */
mix.options({
    uglify: {
        uglifyOptions: {
            compress: false,
            mangle: true,
            output: {
                comments: false
            }
        }
    }
});

mix.webpackConfig({
    resolve: {
        alias: {
            '@components': path.resolve(__dirname, 'resources/js/components'),
            '@views': path.resolve(__dirname, 'resources/js/views/tenant'),
            '@helpers': path.resolve(__dirname, 'resources/js/helpers'),
            '@mixins': path.resolve(__dirname, 'resources/js/mixins'),

            '@viewsModuleSale': path.resolve(__dirname, 'modules/Sale/Resources/assets/js/views'),
            '@viewsModuleFinance': path.resolve(__dirname, 'modules/Finance/Resources/assets/js/views'),
            '@viewsModulePurchase': path.resolve(__dirname, 'modules/Purchase/Resources/assets/js/views'),
            '@viewsModuleExpense': path.resolve(__dirname, 'modules/Expense/Resources/assets/js/views'),
            '@viewsModuleOrder': path.resolve(__dirname, 'modules/Order/Resources/assets/js/views'),
            '@viewsModuleAccount': path.resolve(__dirname, 'modules/Account/Resources/assets/js/views'),
            '@viewsModuleItem': path.resolve(__dirname, 'modules/Item/Resources/assets/js/views'),
            '@viewsModuleHotel': path.resolve(__dirname, 'modules/Hotel/Resources/assets/js/views'),
            '@viewsModuleDocumentary': path.resolve(__dirname, 'modules/DocumentaryProcedure/Resources/assets/js/views'),
            '@viewsModulePayment': path.resolve(__dirname, 'modules/Payment/Resources/assets/js/views'),
            '@viewsModuleMercadoPago': path.resolve(__dirname, 'modules/MercadoPago/Resources/assets/js/views'),
        }
    },
    devtool: false
})

mix.disableSuccessNotifications();
