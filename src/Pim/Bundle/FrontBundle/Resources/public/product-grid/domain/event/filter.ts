import {NormalizedFilter} from 'pimfront/product-grid/domain/model/filter/filter';

export const filterAdded = (filters: NormalizedFilter[]) => {
  return {type: 'FILTER_ADDED', filters};
};
