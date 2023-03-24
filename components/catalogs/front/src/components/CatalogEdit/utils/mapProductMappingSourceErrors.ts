import {CatalogFormErrors} from '../models/CatalogFormErrors';
import {findFirstError} from './findFirstError';
import {ProductMappingErrors} from '../../ProductMapping/models/ProductMappingErrors';

export const mapProductMappingSourceErrors = (errors: CatalogFormErrors, keys: string[]): ProductMappingErrors => {
    const map: ProductMappingErrors = {};

    keys.forEach(key => {
        map[key] = {
            source: findFirstError(errors, `productMapping[${key}][source]`),
            locale: findFirstError(errors, `productMapping[${key}][locale]`),
            scope: findFirstError(errors, `productMapping[${key}][scope]`),
            default: findFirstError(errors, `productMapping[${key}][default]`),
            parameters: {
                label_locale: findFirstError(errors, `productMapping[${key}][parameters][label_locale]`),
                currency: findFirstError(errors, `productMapping[${key}][parameters][currency]`),
                unit: findFirstError(errors, `productMapping[${key}][parameters][unit]`),
            },
        };
    });

    return map;
};
