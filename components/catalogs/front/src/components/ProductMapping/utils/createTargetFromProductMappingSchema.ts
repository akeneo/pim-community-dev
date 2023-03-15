import {Target} from '../models/Target';
import {ProductMappingSchema} from '../models/ProductMappingSchema';

export const createTargetFromProductMappingSchema = (
    targetCode: string,
    productMappingSchema: ProductMappingSchema
): Target => {
    let targetType = productMappingSchema.properties[targetCode].type;
    if ('array' === targetType) {
        targetType += '<' + (productMappingSchema.properties[targetCode].items?.type ?? '') + '>';
    }

    const target: Target = {
        code: targetCode,
        label: productMappingSchema.properties[targetCode].title ?? targetCode,
        type: targetType,
        format: productMappingSchema.properties[targetCode].format ?? null,
    };

    if (undefined !== productMappingSchema.properties[targetCode].description) {
        target.description = productMappingSchema.properties[targetCode].description;
    }
    if (undefined !== productMappingSchema.properties[targetCode].minLength) {
        target.minLength = productMappingSchema.properties[targetCode].minLength;
    }
    if (undefined !== productMappingSchema.properties[targetCode].maxLength) {
        target.maxLength = productMappingSchema.properties[targetCode].maxLength;
    }
    if (undefined !== productMappingSchema.properties[targetCode].pattern) {
        target.pattern = productMappingSchema.properties[targetCode].pattern;
    }
    if (undefined !== productMappingSchema.properties[targetCode].minimum) {
        target.minimum = productMappingSchema.properties[targetCode].minimum;
    }
    if (undefined !== productMappingSchema.properties[targetCode].maximum) {
        target.maximum = productMappingSchema.properties[targetCode].maximum;
    }
    if (undefined !== productMappingSchema.properties[targetCode].enum) {
        target.enum = productMappingSchema.properties[targetCode].enum;
    }

    return target;
};
