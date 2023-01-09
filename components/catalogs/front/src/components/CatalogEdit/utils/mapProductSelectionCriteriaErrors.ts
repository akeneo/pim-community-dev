import {CatalogFormErrors} from '../models/CatalogFormErrors';
import {ProductSelectionErrors} from '../../ProductSelection';
import {findFirstError} from './findFirstError';

export const mapProductSelectionCriteriaErrors = (
    errors: CatalogFormErrors,
    keys: string[]
): ProductSelectionErrors => {
    const map: ProductSelectionErrors = {};

    keys.forEach((key, index) => {
        map[key] = {
            field: findFirstError(errors, `productSelectionCriteria[${index}][field]`),
            operator: findFirstError(errors, `productSelectionCriteria[${index}][operator]`),
            value: findFirstError(errors, `productSelectionCriteria[${index}][value]`),
            locale: findFirstError(errors, `productSelectionCriteria[${index}][locale]`),
            scope: findFirstError(errors, `productSelectionCriteria[${index}][scope]`),
        };
    });

    return map;
};
