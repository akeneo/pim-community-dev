module.exports = {
  setupFiles: ['./setupJest.ts'],
  clearMocks: true,
  moduleFileExtensions: ['js', 'ts', 'tsx'],
  testMatch: ['**/__tests__/src/**/*.[jt]s?(x)', '**/?(*.)+(spec|test).[tj]s?(x)'],
  transform: {'^.+\\.tsx?$': 'ts-jest'},
  moduleDirectories: ['<rootDir>/../../../../../node_modules/'],
  moduleNameMapper: {
    '\\.(svg|css)$': '<rootDir>/__mocks__/fileMock.ts',
  },
  maxWorkers: '3',
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
