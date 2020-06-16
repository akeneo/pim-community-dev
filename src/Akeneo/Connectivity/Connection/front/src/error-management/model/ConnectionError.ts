export const HrefType = 'href';
export const RouteType = 'route';

export type HrefParameter = {
    type: typeof HrefType;
    title: string;
    href: string;
};

export type RouteParameter = {
    type: typeof RouteType;
    title: string;
    route: string;
    routeParameters: {
        [parameterName: string]: string;
    };
};

export type MessageParameters = {
    [needle: string]: HrefParameter | RouteParameter;
};

export type Documentation = {
    message: string;
    parameters: MessageParameters;
};

export type Product = {
    id?: number;
    identifier?: string;
    family?: string;
    label?: string;
};

export type ConnectionErrorContent = {
    message: string;
    message_parameters?: {};
    message_template?: string;
    property?: string;
    documentation?: Array<Documentation>;
    locale?: string;
    scope?: string;
    product?: Product;
    type?: string;
};

export type ConnectionError = {
    id: number;
    timestamp: number;
    content: ConnectionErrorContent;
};
