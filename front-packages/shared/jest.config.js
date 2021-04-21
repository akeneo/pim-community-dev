module.exports = {
  preset: 'ts-jest',
  moduleNameMapper: {
    '\\.(svg|css)$': '<rootDir>/tests/fileMock.ts',
  },
  setupFilesAfterEnv: ['<rootDir>/tests/setupTests.ts'],
  testMatch: ['<rootDir>/src/**/?(*.)+(unit).ts?(x)'],
  collectCoverageFrom: ['<rootDir>/src/**/*.{ts,tsx}'],
};
