import jQuery from 'jquery';

const Routing = require('pim/router');

class FeatureFlags {
  static features: {[feature: string]: boolean} = {};

  static async initialize(): Promise<void> {
    FeatureFlags.features = await jQuery.getJSON(Routing.generate('feature_flag'));
  }

  static isEnabled(feature: string): boolean {
    return Boolean(FeatureFlags.features[feature]);
  }
}

export = FeatureFlags;
