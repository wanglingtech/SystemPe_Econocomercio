/*
 * Crea un shim de node-sass que reexporta dart-sass ("sass").
 *
 * Motivo: sass-loader 6 usa require('node-sass') de forma fija y node-sass
 * 4.x no funciona en Node 18+ (error "Unsupported runtime"). dart-sass
 * expone la misma API (render/renderSync) que necesita sass-loader.
 *
 * Ejecutar antes de compilar assets:  node scripts/prepare-node-sass.js
 */
const fs = require('fs');
const path = require('path');

const root = path.join(__dirname, '..');
const modules = path.join(root, 'node_modules');
const sassDir = path.join(modules, 'sass');
const shimDir = path.join(modules, 'node-sass');

if (!fs.existsSync(sassDir)) {
    console.error('[assets] Falta el paquete "sass". Ejecuta primero: npm install --ignore-scripts');
    process.exit(1);
}

fs.rmSync(shimDir, { recursive: true, force: true });
fs.mkdirSync(shimDir, { recursive: true });

fs.writeFileSync(
    path.join(shimDir, 'package.json'),
    JSON.stringify({ name: 'node-sass', version: '4.14.1', main: 'index.js' }, null, 2)
);
fs.writeFileSync(path.join(shimDir, 'index.js'), "module.exports = require('sass');\n");

console.log('[assets] shim node-sass -> sass (dart-sass) listo.');
