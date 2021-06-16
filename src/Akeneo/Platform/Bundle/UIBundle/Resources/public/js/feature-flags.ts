import jQuery from 'jquery';

const routing = require('routing');

class FeatureFlags {
  static features: {[feature: string]: boolean};

  static async initialize(): Promise<void> {
    if (undefined !== FeatureFlags.features) {
      throw new Error('FeatureFlags is already initialized.');
    }

    FeatureFlags.features = await jQuery.getJSON(routing.generate('feature_flag'));
  }

  static isEnabled(feature: string): boolean {
    if (undefined === FeatureFlags.features) {
      throw new Error('FeatureFlags is not initialized.');
    }

    return Boolean(FeatureFlags.features[feature]);
  }
}

export {FeatureFlags};
