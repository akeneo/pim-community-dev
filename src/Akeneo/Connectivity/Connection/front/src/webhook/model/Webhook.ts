export type Webhook = {
    connectionCode: string;
    url: string | null;
    secret: string | null;
    enabled: boolean;
};
