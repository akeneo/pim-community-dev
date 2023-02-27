export type ProductMappingSchema = {
    properties: {
        [target: string]: {
            title?: string;
            type: string;
            format?: string;
            description?: string;
            minLength?: number;
            maxLength?: number;
        };
    };
};
