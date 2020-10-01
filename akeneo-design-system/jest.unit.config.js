module.exports = {
  clearMocks: true,
  moduleFileExtensions: ['js', 'ts', 'tsx'],
  moduleDirectories: ['node_modules', 'src'],
  roots: ['<rootDir>'],
  setupFilesAfterEnv: ['@testing-library/jest-dom/extend-expect'],
  testMatch: ['**/?(*.)+(unit).ts?(x)'],
  testPathIgnorePatterns: ['/node_modules/', '/generator/'],
  transform: {'^.+\\.tsx?$': 'ts-jest', '^.+\\.mdx$': '@storybook/addon-docs/jest-transform-mdx'},
  transformIgnorePatterns: ['/node_modules/'],
  collectCoverage: true,
  collectCoverageFrom: ['src/**/*.ts?(x)'],
  coveragePathIgnorePatterns: [
      'src/illustrations',
      'src/icons',
      'src/theme',
      'src/storybook',
      'generator',
      'src/all.visual.tsx',
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
