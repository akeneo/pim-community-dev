export interface User {
    get: <T>(data: string) => T;
    set: (key: string, value: string, options: {}) => void;
    refresh: () => Promise<void>;
}
