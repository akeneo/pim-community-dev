import {DependenciesContextProps, NotificationLevel} from '@akeneo-pim-community/shared';

const dependencies: DependenciesContextProps = {
  router: {
    generate: jest.fn((route: string) => route),
    redirect: jest.fn((url: string) => url),
    redirectToRoute: jest.fn((route: string, params?: {[param: string]: any}) => `${route}?${JSON.stringify(params)}`),
  },
  translate: jest.fn((key: string) => key),
  viewBuilder: undefined,
  notify: jest.fn((level: NotificationLevel, message: string): string => `${level} ${message}`),
  user: {
    get: jest.fn((data: string) => {
      switch (data) {
        case 'catalogLocale':
          return 'en_US';
        case 'uiLocale':
          return 'en_US';
        default:
          return data;
      }
    }),
    set: jest.fn(),
  },
  security: {
    isGranted: jest.fn((_acl: string) => true),
  },
  mediator: {
    trigger: jest.fn((event: string) => event),
    on: jest.fn((event: string, _callback: () => void) => event),
    off: jest.fn((event: string, _callback: () => void) => event),
  },
  featureFlags: {
    isEnabled: jest.fn(() => false),
  },
  systemConfiguration: {
    initialize: jest.fn(),
    refresh: jest.fn(),
    get: jest.fn((key: string) => key),
  },
};

export {dependencies};
