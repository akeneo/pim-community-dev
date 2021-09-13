export type ConnectedApp = {
    id: string;
    name: string;
    scopes: string[];
    connection_code: string;
    logo: string;
    author: string;
    categories: string[];
    certified: boolean;
    partner: string | null;
    external_url: string | null;
};
