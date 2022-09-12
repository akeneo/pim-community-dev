import {CatalogFormErrors} from '../models/CatalogFormErrors';
import {findFirstError} from './findFirstError';
import {ProductValueFiltersErrors} from '../../ProductValueFilters';

export const mapProductValueFiltersErrors = (errors: CatalogFormErrors): ProductValueFiltersErrors => ({
    channels: findFirstError(errors, '[product_value_filters][channels]'),
    locales: findFirstError(errors, '[product_value_filters][locales]'),
    currencies: findFirstError(errors, '[product_value_filters][currencies]'),
});
