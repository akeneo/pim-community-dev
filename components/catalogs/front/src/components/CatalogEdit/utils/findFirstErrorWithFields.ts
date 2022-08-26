import {CatalogFormErrors} from '../models/CatalogFormErrors';

export const findFirstErrorWithFields = (errors: CatalogFormErrors, path: string): string | undefined => {
    // the path is transformed in a regex, ex : \[product_selection_criteria\]\[0\]\[value\]\[(.*)\]
    const regex = (path + '[(.*)]').replace(/(\[|\])/g, '\\$1');
    const search = new RegExp(regex);

    return (
        errors.filter(error => error.propertyPath === path || search.exec(error.propertyPath)).shift()?.message ||
        undefined
    );
};
