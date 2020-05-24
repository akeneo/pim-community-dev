const babelLoader = {
    loader: 'babel-loader',
    options: {
        presets: [
            [
                '@babel/preset-env',
                {
                    // targets: {
                    //     /* Chrome 48 release date: 2016-01-20 */
                    //     chrome: '48',
                    //     /* Firefox 47 release date: 2016-01-25 */
                    //     firefox: '47',
                    // },
                    targets: '> 0.25%, not dead',
                },
            ],
            '@babel/preset-react',
        ],
        plugins: ['@babel/plugin-transform-modules-amd', '@babel/plugin-transform-runtime'],
        cacheDirectory: true,
    },
};

const typescriptLoader = {
    loader: 'ts-loader',
    options: {
        transpileOnly: true,
        compilerOptions: {
            allowSyntheticDefaultImports: true,
            esModuleInterop: true,
            jsx: 'react', // Doesn't work with 'preserve'
            lib: ['DOM', 'ES2015', 'ES2016', 'ES2017', 'ES2018'],
            module: 'CommonJS',
            moduleResolution: 'node',
            preserveConstEnums: true,
            skipLibCheck: true,
            sourceMap: true,
            target: 'ES2018',
        },
    },
};

module.exports = {babelLoader, typescriptLoader};
