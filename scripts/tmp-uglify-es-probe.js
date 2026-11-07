const fs = require('fs');
const path = require('path');
const U = require(path.join(process.cwd(), 'node_modules/uglifyjs-webpack-plugin/node_modules/uglify-es'));
console.log('uglify-es', require(path.join(process.cwd(), 'node_modules/uglifyjs-webpack-plugin/node_modules/uglify-es/package.json')).version);

const src = fs.readFileSync('node_modules/vue/dist/vue.runtime.esm.js', 'utf8');
const base = { compress: { warnings: false }, output: { comments: false }, mangle: true };

const variants = {
  'default': base,
  'inline-false': { compress: { warnings: false, inline: false }, output: base.output, mangle: true },
  'inline-0': { compress: { warnings: false, inline: 0 }, output: base.output, mangle: true },
  'mangle-false': { compress: { warnings: false }, output: base.output, mangle: false },
  'no-inline-reduce': { compress: { warnings: false, inline: 0, reduce_vars: false }, output: base.output, mangle: true },
};

for (const [name, opts] of Object.entries(variants)) {
  const t0 = Date.now();
  const res = U.minify({ 'vue.js': src }, opts);
  if (res.error) { console.log(name, 'ERROR', res.error.message || res.error); continue; }
  const code = res.code;
  // síntoma exacto: dentro del bloque inlined hay llamadas s(...) y existe `if(s){` local
  const symptom = /else\{if\(s\)\{if\(1===/.test(code);
  const inlinedChildDiff = code.indexOf(',!1);s(') > -1 || /&&function\([a-z],[a-z],[a-z],[a-z],[a-z]\)\{let/.test(code);
  console.log('---', name, Math.round(code.length / 1024) + 'KB', (Date.now() - t0) + 'ms', 'symptom=' + symptom, 'inlined=' + inlinedChildDiff);
}
