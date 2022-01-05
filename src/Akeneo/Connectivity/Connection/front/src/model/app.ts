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
};

export interface TestApp {
    id: string;
    name: string;
    author: string | null;
    activate_url: string;
    callback_url: string;
    connected: boolean;
}

export type Apps = {
    total: number;
    apps: App[];
};

export interface TestApps {
    total: number;
    apps: TestApp[];
}
