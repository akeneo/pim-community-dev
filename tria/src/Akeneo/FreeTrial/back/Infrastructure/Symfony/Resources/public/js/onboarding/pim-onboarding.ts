export interface PimOnboarding {
  track: (event: string, eventOptions?: object) => void;
  init: () => Promise<void>;
}
