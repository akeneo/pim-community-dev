export interface PimOnboarding {
  registerUser: () => void;
  page: () => void;
  track: (event: string, eventOptions?: object) => void;
  loadLaunchpad: (element: string) => void;
  init: () => void;
}
