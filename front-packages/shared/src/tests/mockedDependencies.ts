import {NotificationLevel, View} from '../DependenciesProvider.type';

const view: View = {
  setElement: () => view,
  render: () => {},
  remove: () => {},
};

const mockedDependencies = {
  router: {
    generate: (route: string) => route,
    redirect: (url: string) => url,
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
};

export {mockedDependencies};
