export type Target = {
    [key: string]: any;
    code: string;
    label: string;
    type: string;
    format: string | null;
    description?: string;
    minLength?: number;
    maxLength?: number;
};
