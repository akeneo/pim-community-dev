import {NotificationLevel} from '../applicationDependenciesProvider.type';

export const dependencies = {
  router: {
    generate: jest.fn((route: string, params?: {[param: string]: any}) => {
      let response = route;
      if (params) {
        response = `${response}?${JSON.stringify(params)}`;
      }
      return response;
    }),
    redirect: jest.fn((url: string) => url),
  },
  translate: jest.fn((key: string) => key),
  viewBuilder: {
    build: (viewName: string) => {
      return Promise.resolve({
        remove: jest.fn(),
        setElement: () => {
          return {
            render: jest.fn(() => viewName),
          };
        },
      });
    },
  },
  notify: jest.fn((level: NotificationLevel, message: string): string => {
    return `${level} ${message}`;
  }),
  user: {
    get: jest.fn((data: string) => {
      switch (data) {
        case 'catalogLocale':
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
  ruleManager: {
    familyAttributesRulesNumberPromise: null,
    getFamilyAttributesRulesNumber: jest.fn(),
  },
};
