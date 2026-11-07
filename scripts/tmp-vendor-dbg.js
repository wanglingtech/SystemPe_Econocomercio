const path = require('path');
const webpack = require('webpack');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');
webpack({
  entry: path.resolve('node_modules/vue/dist/vue.runtime.esm.js'),
  output: { path: path.resolve('.playwright-mcp/tmp-vendor'), filename: 'dbg.js' },
  plugins: [new UglifyJsPlugin({ uglifyOptions: { compress: { inline: false } } })],
}, (err, stats) => {
  if (err) return console.log('ERR', err);
  const info = stats.toJson({ all: false, errors: true });
  console.log((info.errors || []).join('\n').slice(0, 1200));
});
