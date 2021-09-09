export interface CrispAgentInterface {
  is: (action: string) => boolean;
  push: (elements: []) => void;
}

const getCrispAgent = async (): Promise<CrispAgentInterface | null> => {
  // @ts-ignore
  if (typeof window.$crisp === 'undefined' || typeof window.CRISP_WEBSITE_ID === 'undefined') {
    throw new Error('Crisp library is not installed');
  }

  // @ts-ignore
  return window.$crisp ? (window.$crisp as CrispAgent) : null;
};

export {getCrispAgent};
