module.exports = {
  setupFiles: ['./setupJest.ts'],
  clearMocks: true,
  moduleFileExtensions: ['js', 'ts', 'tsx'],
  testMatch: ['**/__tests__/**/*.[jt]s?(x)', '**/?(*.)+(spec|test).[tj]s?(x)'],
  transform: { '^.+\\.tsx?$': 'ts-jest' },
  moduleNameMapper: {
    '\\.(svg)$': '<rootDir>/__mocks__/fileMock.ts',
  },
};
