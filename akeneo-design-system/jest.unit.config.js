module.exports = {
  clearMocks: true,
  moduleFileExtensions: ['js', 'ts', 'tsx'],
  moduleDirectories: ['node_modules', 'src'],
  roots: ['<rootDir>'],
  setupFilesAfterEnv: ['@testing-library/jest-dom/extend-expect'],
  testMatch: ['**/?(*.)+(unit).ts?(x)'],
  testPathIgnorePatterns: ['/node_modules/', '/generator/', 'src/illustrations/', 'src/icons/', '/static/'],
  transform: {
    '^.+\\.tsx?$': 'ts-jest',
    '^.+\\.mdx$': '@storybook/addon-docs/jest-transform-mdx',
    "^.+\\.svg$": "jest-svg-transformer"
  },
  transformIgnorePatterns: ['/node_modules/'],
  collectCoverage: true,
  collectCoverageFrom: ['src/**/*.ts?(x)', '!**/*.visual.ts?(x)'],
  cacheDirectory: '/tmp/jest',
  coveragePathIgnorePatterns: [
      'src/illustrations',
      'src/icons',
      'src/theme',
      'src/storybook',
      'generator',
      'src/shared/PreviewGallery'
  ],
  coverageReporters: ['text-summary', 'html'],
  coverageDirectory: 'coverage',
  coverageThreshold: {
    global: {
      statements: 100,
      functions: 100,
      lines: 100,
    },
  },
};
