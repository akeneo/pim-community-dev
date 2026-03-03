export type App = {
    id: string;
    name: string;
    logo: string;
    author: string;
    partner: string | null;
    description: string | null;
    url: string;
    categories: string[];
    certified: boolean;
    activate_url: string;
    callback_url: string;
    connected: boolean;
    isPending: boolean;
};

export type CustomApp = {
    id: string;
    name: string;
    logo: null;
    author: string | null;
    url: null;
    activate_url: string;
    callback_url: string;
    connected: boolean;
};

export type Apps = {
    total: number;
    apps: App[];
};

export type CustomApps = {
    total: number;
    apps: CustomApp[];
};
