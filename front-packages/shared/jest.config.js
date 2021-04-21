module.exports = {
  preset: 'ts-jest',
  //   globals: {
  //     'ts-jest': {
  //       tsconfig: './tests/tsconfig.json',
  //     },
  //   },
  //   moduleDirectories: ['<rootDir>/../../node_modules/'],
  moduleNameMapper: {
    '\\.(svg|css)$': '<rootDir>/tests/fileMock.ts',
  },
  setupFilesAfterEnv: ['./tests/setupTests.ts'],
  testMatch: ['<rootDir>/src/**/?(*.)+(unit).ts?(x)'],
  collectCoverageFrom: ['<rootDir>/src/**/*.{ts,tsx}'],
};
