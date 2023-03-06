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

    return {
        code: targetCode,
        label: productMappingSchema.properties[targetCode].title ?? targetCode,
        type: targetType,
        format: productMappingSchema.properties[targetCode].format ?? null,
    };
};
