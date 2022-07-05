import {CatalogFormErrors} from '../models/CatalogFormErrors';

export const findFirstError = (errors: CatalogFormErrors, path: string): string | null =>
    errors.filter(error => error.propertyPath === path).shift()?.message || null;
