export interface PimMessaging {
  is: (action: string) => boolean;
  push: (elements: []) => void;
  init: () => void;
}
