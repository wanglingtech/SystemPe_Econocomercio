/*
 * Vuelve a agregar las entradas de CSS en public/mix-manifest.json.
 *
 * El build de Laravel Mix solo compila JS, por lo que al ejecutarse
 * reescribe el manifiesto y elimina /css/app.css y /css/auth.css.
 * Laravel necesita esas entradas para resolver mix('css/app.css').
 *
 * Ejecutar despues del build:  node scripts/fix-mix-manifest.js
 */
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

const publicDir = path.join(__dirname, '..', 'public');
const manifestPath = path.join(publicDir, 'mix-manifest.json');

if (!fs.existsSync(manifestPath)) {
    console.error('[assets] No existe public/mix-manifest.json. Ejecuta primero el build.');
    process.exit(1);
}

const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
const cssFiles = ['/css/app.css', '/css/auth.css'];

for (const rel of cssFiles) {
    const file = path.join(publicDir, rel.replace(/^\//, ''));
    if (!fs.existsSync(file)) {
        console.warn(`[assets] Aviso: no existe ${rel}, se omite del manifiesto.`);
        continue;
    }
    const hash = crypto.createHash('md5').update(fs.readFileSync(file)).digest('hex').slice(0, 20);
    manifest[rel] = `${rel}?id=${hash}`;
}

fs.writeFileSync(manifestPath, JSON.stringify(manifest, null, 4) + '\n');
console.log('[assets] mix-manifest.json actualizado:');
console.log(fs.readFileSync(manifestPath, 'utf8'));
