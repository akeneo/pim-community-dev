export interface AppcuesAgentInterface {
  identify: (uid: string | number, options?: object) => void;
  page: () => void;
  track: (event: string, eventOptions?: object) => void;
  on: (event: string, _callback: (event: object) => void) => void;
  loadLaunchpad: (element: string, options: object) => void;
}

const FeatureFlags = require('pim/feature-flags');

const getAppcuesAgent = async (): Promise<AppcuesAgentInterface | null> => {
  if (!FeatureFlags.isEnabled('free_trial')) {
    return null;
  }

  // @ts-ignore
  if (!window.Appcues || typeof window.Appcues === 'undefined') {
    throw new Error('Appcues library is not installed');
  }

  // @ts-ignore
  return window.Appcues;

};

export {getAppcuesAgent};
