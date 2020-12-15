declare enum NotificationLevel {
    INFO = "info",
    SUCCESS = "success",
    WARNING = "warning",
    ERROR = "error"
}
declare type Notify = (level: NotificationLevel, message: string) => void;
declare type RouteParams = {
    [param: string]: any;
};
declare type Router = {
    generate: (route: string, parameters?: RouteParams) => string;
    redirect: (fragment: string, options?: object) => void;
};
declare type Security = {
    isGranted: (acl: string) => boolean;
};
declare type Translate = (id: string, placeholders?: {
    [name: string]: string;
}, count?: number) => string;
declare type UserContext = {
    get: (data: string) => string;
    set: (key: string, value: string, options: {}) => void;
};
declare type ViewBuilder = {
    build(viewName: string): Promise<any>;
};
declare type Mediator = {
    trigger(event: string): void;
    on(event: string, callback: () => void): void;
};
export { NotificationLevel };
export type { Notify, RouteParams, Router, Security, Translate, UserContext, ViewBuilder, Mediator };
