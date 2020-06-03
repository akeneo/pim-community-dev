export const HrefType = 'href';
export const RouteType = 'route';

export type HrefParameter = {
    type: typeof HrefType;
    title: string;
    href: string;
    needle: string;
};

export type RouteParameter = {
    type: typeof RouteType;
    title: string;
    route: string;
    routeParameters: {
        [parameterName: string]: string;
    };
    needle: string;
};

export type MessageParameters = {
    [needle: string]: HrefParameter | RouteParameter;
};

export type Documentation = {
    message: string;
    parameters: MessageParameters;
};

export type ConnectionError = {
    id: number;
    timestamp: number;
    content: {
        message: string;
        property?: string;
        documentation?: Array<Documentation>;
    };
};
