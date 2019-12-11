export interface User {
    get: (data: string) => string;
    set: (key: string, value: string, options: {}) => void;
}
