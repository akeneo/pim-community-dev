export interface PimOnboarding {
  registerUser: () => void;
  page: () => void;
  track: () => void;
  loadLaunchpad: (element: string) => void;
  init: () => void;
}
