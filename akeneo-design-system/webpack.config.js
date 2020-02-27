var path = require('path');

module.exports = {
    entry: {
        index: './src/index.ts',
        vendor: 'styled-components',
    },
    // Enable sourcemaps for debugging webpack's output.
    devtool: 'source-map',

    output: {
        path: path.join(__dirname, '/lib'),
        filename: '[name].js',
        libraryTarget: 'commonjs2',
    },

    externals: {
        'styled-components': 'styled-components',
    },

    resolve: {
        extensions: ['.ts', '.tsx', '.js'],
    },

    module: {
        rules: [
            {
                test: /\.ts(x?)$/,
                exclude: /node_modules/,
                use: [
                    {
                        loader: 'ts-loader',
                    },
                ],
            },
            // All output '.js' files will have any sourcemaps re-processed by 'source-map-loader'.
            {
                enforce: 'pre',
                test: /\.js$/,
                loader: 'source-map-loader',
            },
        ],
    },
    node: {
        module: 'empty',
        dgram: 'empty',
        dns: 'mock',
        fs: 'empty',
        http2: 'empty',
        net: 'empty',
        tls: 'empty',
        child_process: 'empty',
    },
};
