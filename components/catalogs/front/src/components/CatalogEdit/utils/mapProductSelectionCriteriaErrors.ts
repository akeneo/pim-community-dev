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
            field: findFirstError(errors, `[product_selection_criteria][${index}][field]`),
            operator: findFirstError(errors, `[product_selection_criteria][${index}][operator]`),
            value: findFirstError(errors, `[product_selection_criteria][${index}][value]`),
            locale: findFirstError(errors, `[product_selection_criteria][${index}][locale]`),
            scope: findFirstError(errors, `[product_selection_criteria][${index}][scope]`),
        };
    });

    return map;
};
