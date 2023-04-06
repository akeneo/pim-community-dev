import {Target} from '../models/Target';
import {ProductMappingSchema} from '../models/ProductMappingSchema';

export const createTargetFromProductMappingSchema = (
    targetCode: string,
    productMappingSchema: ProductMappingSchema
): Target => {
    const properties = productMappingSchema.properties[targetCode];

    const target: Target = {
        code: targetCode,
        label: properties.title ?? targetCode,
        type: properties.type,
        format: properties.format ?? null,
    };

    if (undefined !== properties.description) {
        target.description = properties.description;
    }
    if (undefined !== properties.minLength) {
        target.minLength = properties.minLength;
    }
    if (undefined !== properties.maxLength) {
        target.maxLength = properties.maxLength;
    }
    if (undefined !== properties.pattern) {
        target.pattern = properties.pattern;
    }
    if (undefined !== properties.minimum) {
        target.minimum = properties.minimum;
    }
    if (undefined !== properties.maximum) {
        target.maximum = properties.maximum;
    }
    if (undefined !== properties.enum) {
        target.enum = properties.enum;
    }

    if ('array' === properties.type) {
        let itemType = properties.items?.type ?? '';
        itemType = properties.items?.format ? itemType + '+' + properties.items.format : itemType;

        target.type += `<${itemType}>`;

        if (undefined !== properties.items?.enum) {
            target.enum = properties.items.enum;
        }
    }

    return target;
};
