export type Webhook = {
    connectionCode: string;
    url: string;
    secret: string;
    enabled: boolean;
    connectionImage: string | null;
};
