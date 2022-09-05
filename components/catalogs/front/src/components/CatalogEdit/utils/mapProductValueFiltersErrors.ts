import {CatalogFormErrors} from '../models/CatalogFormErrors';
import {findFirstError} from './findFirstError';
import {ProductValueFiltersErrors} from '../../ProductValueFilters';

export const mapProductValueFiltersErrors = (errors: CatalogFormErrors): ProductValueFiltersErrors => ({
    channel: findFirstError(errors, '[product_value_filters][channel]'),
});
