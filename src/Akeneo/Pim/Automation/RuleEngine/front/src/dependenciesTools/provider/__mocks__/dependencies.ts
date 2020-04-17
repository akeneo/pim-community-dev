import { NotificationLevel } from "../applicationDependenciesProvider.type";

export const dependencies = {
  router: {
    generate: jest.fn((route: string) => route),
    redirect: jest.fn((url: string) => url)
  },
  translate: jest.fn((key: string) => key),
  viewBuilder: {
    build: (viewName: string) => {
      return Promise.resolve({
        remove: jest.fn(),
        setElement: () => {
          return {
            render: jest.fn(() => viewName)
          };
        }
      });
    }
  },
  notify: jest.fn((level: NotificationLevel, message: string): string => {
    return `${level} ${message}`;
  }),
  user: {
    get: jest.fn((data: string) => data),
    set: jest.fn((): void => {})
  },
  security: {
    isGranted: jest.fn((acl: boolean) => acl)
  }
};
