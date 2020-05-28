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
    params: {
        [parameterName: string]: string;
    };
};

export type Documentation = {
    message: string;
    params: Array<HrefParameter | RouteParameter>;
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
