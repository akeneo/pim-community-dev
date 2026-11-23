require('jest-fetch-mock').enableMocks();
import fetchMock from 'jest-fetch-mock';

let featureFlags: typeof import('./feature-flags').featureFlags;

beforeEach(() => {
  fetchMock.resetMocks();
  jest.isolateModules(() => {
    featureFlags = require('./feature-flags').featureFlags;
  });
});

test('it throws when used before initialization', () => {
  expect(featureFlags.isInitialized()).toBe(false);
  expect(() => featureFlags.isEnabled('free_trial')).toThrow('FeatureFlags is not initialized.');
});

test('it loads the feature flags once', async () => {
  fetchMock.mockResponseOnce(JSON.stringify({free_trial: false, identifier_generator: true}));

  await Promise.all([featureFlags.initialize(), featureFlags.initialize()]);
  await featureFlags.initialize();

  expect(fetchMock).toHaveBeenCalledTimes(1);
  expect(fetchMock.mock.calls[0][0]).toBe('/configuration/feature-flags');
  expect(featureFlags.isInitialized()).toBe(true);
  expect(featureFlags.isEnabled('identifier_generator')).toBe(true);
  expect(featureFlags.isEnabled('free_trial')).toBe(false);
  expect(featureFlags.isEnabled('unknown')).toBe(false);
});

test('it can retry after a failure', async () => {
  fetchMock.mockResponseOnce('', {status: 500});
  await expect(featureFlags.initialize()).rejects.toThrow();

  fetchMock.mockResponseOnce(JSON.stringify({free_trial: true}));
  await featureFlags.initialize();
  expect(featureFlags.isEnabled('free_trial')).toBe(true);
});
