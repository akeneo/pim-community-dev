const FeatureFlags = jest.fn();
FeatureFlags.isEnabled = jest.fn((feature: string) => false);

module.exports = FeatureFlags;
