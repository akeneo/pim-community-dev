import {NotificationLevel, View} from '../DependenciesProvider.type';

const view: View = {
  setElement: () => view,
  render: () => {},
  remove: () => {},
  setData: () => {},
};

const mockedDependencies = {
  router: {
    generate: (route: string) => route,
    redirect: (url: string) => url,
    redirectToRoute: (route: string) => route,
  },
  translate: (key: string) => key,
  viewBuilder: {
    build: (_viewName: string) => Promise.resolve(view),
  },
  notify: (level: NotificationLevel, message: string): string => {
    return `${level} ${message}`;
  },
  user: {
    get: (data: string) => {
      switch (data) {
        case 'catalogLocale':
          return 'en_US';
        case 'uiLocale':
          return 'en_US';
        case 'timezone':
          return 'UTC';
        default:
          return data;
      }
    },
    set: () => {},
  },
  security: {
    isGranted: (_acl: string) => true,
  },
  mediator: {
    trigger: (event: string) => event,
    on: (event: string, _callback: () => void) => event,
    off: (event: string, _callback: () => void) => event,
  },
  featureFlags: {
    isEnabled: (_feature: string) => true,
  },
  analytics: {
    track: (event: string) => event,
  },
};

export {mockedDependencies};
