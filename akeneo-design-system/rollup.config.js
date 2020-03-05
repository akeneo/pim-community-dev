// import typescript from 'rollup-plugin-typescript2';
import commonjs from 'rollup-plugin-commonjs';
import babel from 'rollup-plugin-babel';
import analyze from 'rollup-plugin-analyzer';
import resolve from 'rollup-plugin-node-resolve';
import {terser} from 'rollup-plugin-terser';
import replace from '@rollup/plugin-replace';

import pkg from './package.json';

const extensions = ['.js', '.ts', '.tsx'];
const babelConfig = {
    extensions,
    test: /\.ts(x?)$/,
    include: ['src/**/*'],
    exclude: 'node_modules/**',
};

const env = process.env.NODE_ENV;

const plugins = [
    babel(babelConfig),
    resolve({extensions}),
    commonjs(),
    replace({'process.env.NODE_ENV': JSON.stringify(env)}),
];

const external = ['styled-components'];

const rollUpConf = args => {
    if (args['config-analyze']) {
        plugins.push(
            analyze({
                showExports: true,
                summaryOnly: true,
            })
        );
    }
    if (args['config-production']) {
        plugins.push(terser());
    }
    return {
        input: 'src/index.ts',
        output: [
            {
                file: pkg.main,
                format: 'cjs',
                exports: 'named',
                sourcemap: true,
            },
            {
                file: pkg.module,
                format: 'es',
                exports: 'named',
                sourcemap: true,
            },
        ],
        plugins,
        external,
    };
};

export default rollUpConf;
