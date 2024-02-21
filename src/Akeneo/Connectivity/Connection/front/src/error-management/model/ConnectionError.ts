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
    style: typeof DocumentationStyleText | typeof DocumentationStyleInformation;
};

export type Product = {
    id: number | null;
    identifier: string;
    family: string | null;
    label: string;
};

export type ConnectionErrorContent = {
    message: string;
    message_parameters?: {[key: string]: string};
    message_template?: string;
    documentation?: Array<Documentation>;
    locale?: string | null;
    scope?: string | null;
    product?: Product;
    type: typeof ErrorMessageViolationType | typeof ErrorMessageDomainType;
};

export type ConnectionError = {
    id: number;
    timestamp: number;
    content: ConnectionErrorContent;
};
