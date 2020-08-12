module.exports = {
  clearMocks: true,
  coverageDirectory: 'coverage',
  moduleFileExtensions: [
      'js',
      'ts',
      'tsx',
  ],
  roots: ['<rootDir>'],
  setupFilesAfterEnv: ['@testing-library/jest-dom/extend-expect'],
  testMatch: ['**/__tests__/**/*.ts?(x)', '**/?(*.)+(spec|test).ts?(x)'],
  testPathIgnorePatterns: ['/node_modules/'],
  transform: {'^.+\\.tsx?$': 'ts-jest', '^.+\\.mdx$': '@storybook/addon-docs/jest-transform-mdx'},
  transformIgnorePatterns: ['/node_modules/'],
};
