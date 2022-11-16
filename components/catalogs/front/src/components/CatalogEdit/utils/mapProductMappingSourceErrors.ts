import {CatalogFormErrors} from '../models/CatalogFormErrors';
import {findFirstError} from './findFirstError';
import {ProductMappingErrors} from '../../ProductMapping/models/ProductMappingErrors';

export const mapProductMappingSourceErrors = (errors: CatalogFormErrors, keys: string[]): ProductMappingErrors => {
    const map: ProductMappingErrors = {};

    keys.forEach((key) => {
        map[key] = {
            source: findFirstError(errors, `[product_mapping][${key}][source]`),
            locale: findFirstError(errors, `[product_mapping][${key}][locale]`),
            scope: findFirstError(errors, `[product_mapping][${key}][scope]`),
        };
    });

    return map;
};
