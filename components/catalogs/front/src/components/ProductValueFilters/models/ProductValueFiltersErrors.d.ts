import {ProductValueFiltersValues} from './ProductValueFiltersValues';

export type ProductValueFiltersErrors = {
    [key in keyof ProductValueFiltersValues]?: string;
};
