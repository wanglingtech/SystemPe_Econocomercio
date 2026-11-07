const path = require('path');
const fs = require('fs');
const webpack = require('webpack');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');

const entry = path.resolve(__dirname, '../node_modules/vue/dist/vue.runtime.esm.js');
const outDir = path.resolve(__dirname, '../.playwright-mcp/tmp-vendor');

const variants = {
  'p-inline-false': { compress: { inline: false } },
  'p-default': { compress: {} },
  'p-no-compress': false,
  'p-reduce-vars-off': { compress: { inline: false, reduce_vars: false } },
};

function build(name, compress) {
  return new Promise((resolve) => {
    const uglifyOptions = {};
    if (compress !== false) uglifyOptions.compress = compress;
    webpack({
      entry,
      output: { path: outDir, filename: name + '.js' },
      plugins: [new UglifyJsPlugin({ uglifyOptions })],
    }, (err, stats) => {
      if (err) { console.log(name, 'ERR', err.message.slice(0, 150)); return resolve(); }
      const info = stats.toJson({ all: false, errors: true });
      if (info.errors && info.errors.length) { console.log(name, 'ERRORS', info.errors[0].slice(0, 150).replace(/\n/g, ' ')); return resolve(); }
      const code = fs.readFileSync(path.join(outDir, name + '.js'), 'utf8');
      console.log('---', name, Math.round(code.length / 1024) + 'KB', 'symptom=' + /else\{if\(s\)\{if\(1===/.test(code), 's(n.text)@' + code.indexOf('s(n.text)'));
      resolve();
    });
  });
}

(async () => { for (const [n, c] of Object.entries(variants)) await build(n, c); })();
