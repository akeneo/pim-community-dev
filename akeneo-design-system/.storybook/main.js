const path = require('path');

module.exports = {
    stories: ['../src/**/*.stories.mdx'],
    addons: ['@storybook/addon-actions', '@storybook/addon-links', '@storybook/addon-knobs', '@storybook/addon-docs'],
    webpackFinal: async config => {
        config.resolve.extensions.push('.ts', '.tsx');

        config.module.rules.push({
            test: /\.(ts|tsx)$/,
            use: [
                {
                    loader: require.resolve('ts-loader'),
                    options: {
                        configFile: path.resolve(__dirname, '../tsconfig.json'),
                    },
                },
                {
                    loader: require.resolve('react-docgen-typescript-loader'),
                    options: {
                        tsconfigPath: path.resolve(__dirname, '../tsconfig.json'),
                    },
                },
            ],
        });

        return config;
    },
};
