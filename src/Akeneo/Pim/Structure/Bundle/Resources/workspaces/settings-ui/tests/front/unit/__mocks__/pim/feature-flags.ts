const FeatureFlags = jest.fn();

// @ts-ignore
FeatureFlags.isEnabled = jest.fn((feature: string) => false);

module.exports = FeatureFlags;
