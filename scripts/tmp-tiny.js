const path = require('path');
const webpack = require('webpack');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
const fs = require('fs');
fs.mkdirSync('.playwright-mcp/tmp-vendor', { recursive: true });
fs.writeFileSync('.playwright-mcp/tmp-vendor/tiny.js', 'function add(a, b) { return a + b; } console.log(add(1, 2));');
webpack({
  entry: path.resolve('.playwright-mcp/tmp-vendor/tiny.js'),
  output: { path: path.resolve('.playwright-mcp/tmp-vendor'), filename: 'out.js' },
  plugins: [new UglifyJsPlugin({ uglifyOptions: { compress: { inline: false } } })],
}, (err, stats) => {
  if (err) { console.log('FATAL', err.message); return; }
  const info = stats.toJson({ all: false, errors: true });
  if (info.errors && info.errors.length) console.log('ERRORS', info.errors.join(' | ').slice(0, 300));
  else console.log('OK', fs.readFileSync('.playwright-mcp/tmp-vendor/out.js', 'utf8').slice(0, 80));
});
