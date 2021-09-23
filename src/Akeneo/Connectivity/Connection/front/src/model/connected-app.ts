export type ConnectedApp = {
    id: string;
    name: string;
    scopes: any;
    connection_code: string;
    logo: string;
    author: string;
    categories: string[];
    certified: boolean;
    partner: string | null;
};
