const TsconfigPathsPlugin = require('tsconfig-paths-webpack-plugin');

module.exports = {
  'stories': [
    '../src/Introduction.stories.mdx',
    '../src/**/*.stories.mdx',
  ],
  'addons': [
    {
      name: '@storybook/addon-docs',
      options: { transcludeMarkdown: true },
    },
    '@storybook/addon-links',
    '@storybook/addon-essentials',
    'themeprovider-storybook/register',
    '@storybook/addon-a11y'
  ],
  webpackFinal: async (config) => {
    return {
      ...config,
      resolve: {
        ...config.resolve,
        plugins: [
          ...config.resolve.plugins,
          new TsconfigPathsPlugin({
            baseUrl: './src'
          })
        ]
      }};
  },
}
