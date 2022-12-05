import {CatalogFormErrors} from '../components/CatalogEdit/models/CatalogFormErrors';

export const findFirstError = (errors: CatalogFormErrors, path: string): string | undefined =>
    errors.filter(error => error.propertyPath.startsWith(path)).shift()?.message || undefined;
