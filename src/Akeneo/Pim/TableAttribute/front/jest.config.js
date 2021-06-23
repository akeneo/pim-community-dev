module.exports = {
  setupFiles: ['./setupJest.ts'],
  clearMocks: true,
  moduleFileExtensions: ['js', 'ts', 'tsx'],
  testMatch: ['**/__tests__/**/*.[jt]s?(x)', '**/?(*.)+(spec|test).[tj]s?(x)'],
  testPathIgnorePatterns: ['/__tests__/src/factories/'],
  transform: {'^.+\\.tsx?$': 'ts-jest'},
  moduleDirectories: ['<rootDir>/../../../../../node_modules/'],
  moduleNameMapper: {
    '\\.(svg|css)$': '<rootDir>/__mocks__/fileMock.ts',
  },
  collectCoverage: true,
  collectCoverageFrom: [
    'src/**/*.{js,jsx,ts,tsx}',
    '!src/fetchers/**/*.{js,jsx,ts,tsx}',
    '!src/legacy/*.{js,jsx,ts,tsx}',
  ],
  coverageThreshold: {
    global: {
      statements: 100,
      functions: 100,
      lines: 100,
    },
  },
};
