const path = require('path');
const fs = require('fs');
const webpack = require('webpack');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');

const entry = path.resolve(__dirname, '../node_modules/vue/dist/vue.runtime.esm.js');
const outDir = path.resolve(__dirname, '../.playwright-mcp/tmp-vendor');

const variants = {
  default: { compress: { warnings: false } },
  'inline-false': { compress: { warnings: false, inline: false } },
  'no-compress': false,
};

function build(name, compress) {
  return new Promise((resolve) => {
    const uglifyOptions = { sourceMap: false, output: { comments: false } };
    if (compress !== false) uglifyOptions.compress = compress;
    webpack({
      entry,
      output: { path: outDir, filename: name + '.js' },
      plugins: [new UglifyJsPlugin({ uglifyOptions })],
    }, (err, stats) => {
      if (err) { console.log(name, 'ERR', err.message); return resolve(); }
      const info = stats.toJson({ all: false, errors: true, warnings: false });
      if (info.errors && info.errors.length) { console.log(name, 'ERRORS', info.errors[0].slice(0, 200)); return resolve(); }
      const code = fs.readFileSync(path.join(outDir, name + '.js'), 'utf8');
      const symptom = /else\{if\(s\)\{if\(1===/.test(code);
      const idx = code.indexOf('s(n.text)');
      console.log('---', name, Math.round(code.length / 1024) + 'KB', 'symptom=' + symptom, 's(n.text)@' + idx);
      resolve();
    });
  });
}

(async () => {
  for (const [name, compress] of Object.entries(variants)) {
    await build(name, compress);
  }
})();
