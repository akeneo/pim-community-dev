import jQuery from 'jquery';

const routing = require('routing');
const Mediator = require('oro/mediator');

class FeatureFlags {
  static features: {[feature: string]: boolean};

  static async initialize(): Promise<void> {
    if (undefined !== FeatureFlags.features) {
      throw new Error('FeatureFlags is already initialized.');
    }

    await FeatureFlags.setFeatureFlags();

    Mediator.on('route_complete', (): void => {
      FeatureFlags.setFeatureFlags();
    });
  }

  static isEnabled(feature: string): boolean {
    if (undefined === FeatureFlags.features) {
      throw new Error('FeatureFlags is not initialized.');
    }

    return Boolean(FeatureFlags.features[feature]);
  }

  private static async setFeatureFlags(): Promise<void> {
    FeatureFlags.features = await jQuery.getJSON(routing.generate('feature_flag'));
  }
}

export = FeatureFlags;
