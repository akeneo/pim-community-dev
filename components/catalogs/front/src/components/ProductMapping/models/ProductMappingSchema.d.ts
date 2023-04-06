export type ProductMappingSchema = {
    properties: {
        [target: string]: {
            title?: string;
            type: string;
            items?: {
                type: string;
                format?: string;
                enum?: string[];
            };
            format?: string;
            description?: string;
            minLength?: number;
            maxLength?: number;
            pattern?: string;
            minimum?: number;
            maximum?: number;
            enum?: string[] | number[];
        };
    };

    required?: string[];
};
