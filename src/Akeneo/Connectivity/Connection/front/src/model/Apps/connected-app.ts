export type ConnectedApp = {
    id: string;
    name: string;
    scopes: string[];
    connection_code: string;
    logo: string | null;
    author: string | null;
    user_group_name: string;
    categories: string[];
    certified: boolean;
    partner: string | null;
    activate_url?: string;
    is_test_app: boolean;
    is_pending: boolean;
};
