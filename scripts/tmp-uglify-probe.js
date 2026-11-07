const fs = require('fs');
const UglifyJS = require('uglify-js');
console.log('uglify-js', require('uglify-js/package.json').version);

const src = fs.readFileSync('node_modules/vue/dist/vue.runtime.esm.js', 'utf8');
const base = {
  compress: { warnings: false },
  output: { comments: false },
  sourceMap: false,
};

const variants = {
  'default': base,
  'inline-false': { compress: { warnings: false, inline: false }, output: base.output },
  'inline-0-reduce': { compress: { warnings: false, inline: 0, reduce_vars: false, reduce_funcs: false }, output: base.output },
  'no-compress': { compress: false, output: base.output },
};

for (const [name, opts] of Object.entries(variants)) {
  const t0 = Date.now();
  const res = UglifyJS.minify(src, opts);
  if (res.error) { console.log(name, 'ERROR', res.error.message); continue; }
  const code = res.code;
  const hasPatch = code.indexOf('s(n.text)');
  const hasAnonInline = code.indexOf('s(n.text)?') > -1;
  // síntoma: variable local s en el mismo ámbito que la llamada s(n.text)
  const seg = hasPatch >= 0 ? code.slice(hasPatch - 400, hasPatch + 40) : '';
  console.log('---', name, Math.round(code.length / 1024) + 'KB', (Date.now() - t0) + 'ms', 's(n.text)@' + hasPatch);
  if (seg) console.log(seg.slice(-260).replace(/\n/g, ' '));
}
