const path = require('path');
const minify = require(path.join(process.cwd(), 'node_modules/uglifyjs-webpack-plugin/dist/uglify/minify')).default;
const fs = require('fs');
const src = fs.readFileSync('node_modules/vue/dist/vue.runtime.esm.js', 'utf8');
const all = { 'vue.js': src };

const tests = {
  'mix-exact': { sourceMap: false, compress: { warnings: false, inline: false }, output: { comments: false } },
  'no-sourcemap-key': { compress: { warnings: false, inline: false }, output: { comments: false } },
  'no-warnings': { compress: { inline: false }, output: { comments: false } },
  'default-compress': { compress: {}, output: { comments: false } },
};

(async () => {
  for (const [name, opts] of Object.entries(tests)) {
    try {
      const res = await minify(all, opts);
      const code = res.code || '';
      const idx = code.indexOf('s(n.text)');
      console.log(name, '=>', Math.round(code.length / 1024) + 'KB', 's(n.text)@' + idx, 'symptom=' + /else\{if\(s\)\{if\(1===/.test(code));
    } catch (e) {
      console.log(name, '=> ERROR:', (e && e.message || String(e)).slice(0, 120));
    }
  }
})();
