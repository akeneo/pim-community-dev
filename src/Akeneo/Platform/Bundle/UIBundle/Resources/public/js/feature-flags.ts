import jQuery from 'jquery';
import {featureFlags} from '@akeneo-pim-community/shared';

/**
 * @deprecated Legacy module ID kept for backward compatibility.
 * The implementation lives in @akeneo-pim-community/shared; this shim only adapts promises for legacy callers.
 */
const FeatureFlags = {
  initialize: (): JQueryPromise<void> => jQuery.when(featureFlags.initialize()),
  isEnabled: (feature: string): boolean => featureFlags.isEnabled(feature),
};

export = FeatureFlags;
