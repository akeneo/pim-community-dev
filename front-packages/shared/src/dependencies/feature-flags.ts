import {FeatureFlags} from '../DependenciesProvider.type';
import {getJson} from './http';

const FEATURE_FLAGS_URL = '/configuration/feature-flags';

type Features = {[feature: string]: boolean};

let features: Features | undefined = undefined;
let loading: Promise<void> | null = null;

/**
 * Enabled features of the PIM, historically `pim/feature-flags`.
 * Initialization is idempotent: concurrent or repeated calls share the same request.
 */
const featureFlags: FeatureFlags & {initialize: () => Promise<void>; isInitialized: () => boolean} = {
  initialize: () => {
    if (null === loading) {
      loading = getJson<Features>(FEATURE_FLAGS_URL).then(
        response => {
          features = response;
        },
        error => {
          loading = null;
          throw error;
        }
      );
    }

    return loading;
  },
  isInitialized: () => undefined !== features,
  isEnabled: (feature: string) => {
    if (undefined === features) {
      throw new Error('FeatureFlags is not initialized.');
    }

    return Boolean(features[feature]);
  },
};

export {featureFlags};
