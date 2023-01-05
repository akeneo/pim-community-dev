import {CatalogFormErrors} from '../models/CatalogFormErrors';
import {findFirstError} from './findFirstError';
import {ProductValueFiltersErrors} from '../../ProductValueFilters';

export const mapProductValueFiltersErrors = (errors: CatalogFormErrors): ProductValueFiltersErrors => ({
    channels: findFirstError(errors, 'productValueFilters[channels]'),
    locales: findFirstError(errors, 'productValueFilters[locales]'),
    currencies: findFirstError(errors, 'productValueFilters[currencies]'),
});
