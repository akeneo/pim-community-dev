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
};

export type Apps = {
    total: number;
    apps: App[];
};
