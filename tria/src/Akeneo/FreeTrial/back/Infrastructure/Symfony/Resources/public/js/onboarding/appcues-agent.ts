export interface AppcuesAgentInterface {
  identify: (uid: string | number, options?: object) => void;
  page: () => void;
  track: (event: string, eventOptions?: object) => void;
  on: (event: string, _callback: (event: object) => void) => void;
  loadLaunchpad: (element: string, options: object) => void;
}

const getAppcuesAgent = async (): Promise<AppcuesAgentInterface | null> => {
  // @ts-ignore
  return window.Appcues ? (window.Appcues as AppcuesAgent) : null;
};

export {getAppcuesAgent};
