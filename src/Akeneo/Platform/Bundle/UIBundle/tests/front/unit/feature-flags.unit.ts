import FeatureFlags from 'pimui/js/feature-flags';
import jQuery from 'jquery';

const Mediator = require('oro/mediator');

jest.mock('oro/mediator');

describe('FeatureFlags', () => {
  describe('initialize', () => {
    test('it should set feature flags', async () => {
      const mockResponse = {feature1: true, feature2: false};
      jQuery.getJSON = jest.fn().mockResolvedValue(mockResponse);

      const setFeatureFlagsMock = jest.spyOn(FeatureFlags, 'setFeatureFlags').mockImplementation(async () => {
        FeatureFlags.features = mockResponse;
      });

      const mediatorOnSpy = jest.spyOn(Mediator, 'on');

      await FeatureFlags.initialize();

      expect(setFeatureFlagsMock).toHaveBeenCalledTimes(1);
      expect(FeatureFlags.features).toEqual(mockResponse);
      expect(mediatorOnSpy).toHaveBeenCalledWith('route_start', expect.any(Function));
    });

    test('it should throw an error if FeatureFlags is already initialized', async () => {
      FeatureFlags.features = {};

      await expect(FeatureFlags.initialize()).rejects.toThrowError('FeatureFlags is already initialized.');
    });
  });

  describe('isEnabled', () => {
    test('it should throw an error if FeatureFlags is not initialized', () => {
      FeatureFlags.features = undefined;

      expect(() => FeatureFlags.isEnabled('feature1')).toThrowError('FeatureFlags is not initialized.');
    });

    test('it should return the correct value for a feature', () => {
      FeatureFlags.features = {feature1: true, feature2: false};

      expect(FeatureFlags.isEnabled('feature1')).toBe(true);
      expect(FeatureFlags.isEnabled('feature2')).toBe(false);
    });
  });
});
