const path = require('path');

module.exports = {
  entry: {
    App: path.resolve(__dirname, './resources/js/App.js')
  },
  mode: 'development',
  output: {
    path: path.resolve(__dirname, './public/'),
    filename: 'js/[name].js'
  },
  module:{
    rules: [
      {
        test: /\.js$/,
        use: 'babel-loader',
        exclude: /node_modules/
      },
      {
        test: /\.scss$/,
        use: [
          'style-loader',
          'css-loader',
          'sass-loader'
        ],
        exclude: /node_modules/
      }
    ]
  },
  devtool: 'inline-source-map'
};
