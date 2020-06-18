export const HrefType = 'href';
export const RouteType = 'route';
export const DocumentationStyleText = 'text';
export const DocumentationStyleInformation = 'information';
export const ErrorMessageDomainType = 'domain_error';
export const ErrorMessageViolationType = 'violation_error';

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
    style: 'text' | 'information';
};

export type Product = {
    id: number;
    identifier: string;
    family?: string;
    label: string;
};

export type ConnectionErrorContent = {
    message: string;
    message_parameters?: {[key: string]: string};
    message_template?: string;
    documentation?: Array<Documentation>;
    locale?: string;
    scope?: string;
    product?: Product;
    type: 'violation_error' | 'domain_error';
};

export type ConnectionError = {
    id: number;
    timestamp: number;
    content: ConnectionErrorContent;
};
