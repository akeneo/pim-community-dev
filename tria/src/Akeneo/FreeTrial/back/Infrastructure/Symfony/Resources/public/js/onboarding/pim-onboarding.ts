export interface PimOnboarding {
  registerUser: () => void;
  page: () => void;
  track: (event: string, eventOptions?: object) => void;
  on: (event: string, _callback: (event: object) => void) => void;
  loadLaunchpad: (element: string) => void;
  init: () => void;
}
