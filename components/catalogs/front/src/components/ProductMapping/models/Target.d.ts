export type Target = {
    code: string;
    label: string;
    type: string;
    format: string | null;
    description?: string;
    minLength?: number;
    maxLength?: number;
    pattern?: string;
};
