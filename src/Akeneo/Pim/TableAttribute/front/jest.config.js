module.exports = {
  setupFiles: ['./setupJest.ts'],
  clearMocks: true,
  moduleFileExtensions: ['js', 'ts', 'tsx'],
  testMatch: ['**/__tests__/**/*.[jt]s?(x)', '**/?(*.)+(spec|test).[tj]s?(x)'],
  testPathIgnorePatterns: ['/__tests__/src/factories/'],
  transform: {'^.+\\.tsx?$': 'ts-jest'},
  moduleDirectories: ['<rootDir>/../../../../../../node_modules/'],
  moduleNameMapper: {
    '\\.(svg|css)$': '<rootDir>/__mocks__/fileMock.ts',
  },
};
