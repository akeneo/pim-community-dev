import {TargetTypes} from './AllowedTargetTypes';

export type ProductMappingSchema = {
    properties: {
        [target: string]: {
            title?: string;
            type: TargetTypes;
        };
    };
};
