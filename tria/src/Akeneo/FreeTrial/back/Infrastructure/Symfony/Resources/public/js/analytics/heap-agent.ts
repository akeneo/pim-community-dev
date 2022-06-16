export interface HeapAgent {
  identify: (username: string) => void;
  addUserProperties: (properties: object) => void;
}

const FeatureFlags = require('pim/feature-flags');

const getHeapAgent = async (): Promise<HeapAgent | null> => {
  if (!FeatureFlags.isEnabled('free_trial')) {
    return null;
  }

  // @ts-ignore
  if (!window.heap || typeof window.heap === 'undefined') {
    throw new Error('Heap library is not installed');
  }

  // @ts-ignore
  return window.heap as HeapAgent;
};

export {getHeapAgent};
