export type ConnectedApp = {
    id: string;
    name: string;
    scopes: string[];
    connection_code: string;
    logo: string;
    author: string;
    user_group_name: string;
    categories: string[];
    certified: boolean;
    partner: string | null;
    activate_url?: string;
};
