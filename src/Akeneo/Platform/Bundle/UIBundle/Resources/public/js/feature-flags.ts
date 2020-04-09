import jQuery from 'jquery';

const Routing = require('pim/router');

class FeatureFlags {
  static features: {[feature: string]: boolean};

  static async initialize(): Promise<void> {
    if (undefined !== FeatureFlags.features) {
      throw new Error('FeatureFlags is already initialized.');
    }

    FeatureFlags.features = await jQuery.getJSON(Routing.generate('feature_flag'));
  }

  static isEnabled(feature: string): boolean {
    if (undefined === FeatureFlags.features) {
      throw new Error('FeatureFlags is not initialized.');
    }

    return Boolean(FeatureFlags.features[feature]);
  }
}

export = FeatureFlags;
