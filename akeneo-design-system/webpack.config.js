var path = require("path");

module.exports = {
  entry: "./src/index.ts",
  // Enable sourcemaps for debugging webpack's output.
  devtool: "source-map",

  output: {
    path: path.join(__dirname, "/lib"),
    filename: "index.js",
    libraryTarget: "commonjs2"
  },

  resolve: {
    extensions: [".ts", ".tsx", ".js"]
  },

  module: {
    rules: [
      {
        test: /\.ts(x?)$/,
        exclude: /node_modules/,
        use: [
          {
            loader: "ts-loader"
          }
        ]
      },
      // All output '.js' files will have any sourcemaps re-processed by 'source-map-loader'.
      {
        enforce: "pre",
        test: /\.js$/,
        loader: "source-map-loader"
      }
    ]
  }
};
