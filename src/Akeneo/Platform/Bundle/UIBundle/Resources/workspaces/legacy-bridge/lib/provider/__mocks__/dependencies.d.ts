/// <reference types="jest" />
import { NotificationLevel } from '../DependenciesProvider.type';
declare const dependencies: {
    router: {
        generate: jest.Mock<string, [route: string]>;
        redirect: jest.Mock<string, [url: string]>;
    };
    translate: jest.Mock<string, [key: string]>;
    viewBuilder: {
        build: (viewName: string) => Promise<{
            remove: jest.Mock<any, any>;
            setElement: () => {
                render: jest.Mock<string, []>;
            };
        }>;
    };
    notify: jest.Mock<string, [level: NotificationLevel, message: string]>;
    user: {
        get: jest.Mock<string, [data: string]>;
        set: jest.Mock<any, any>;
    };
    security: {
        isGranted: jest.Mock<string, [acl: string]>;
    };
    mediator: {
        trigger: jest.Mock<string, [event: string]>;
        on: jest.Mock<string, [event: string, _callback: () => void]>;
    };
};
export { dependencies };
