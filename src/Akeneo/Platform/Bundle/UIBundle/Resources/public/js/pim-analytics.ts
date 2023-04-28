import {Analytics} from '@akeneo-pim-community/shared';

const PimAnalytics: Analytics = {
  track(_event: string, _properties?: object) {
    // track user actions in the application
  },

  appcuesTrack(_event: string, _properties?: object) {
    // appcues track user actions in the application
  },
};

export = PimAnalytics;
