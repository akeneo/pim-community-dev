import {NormalizedFilter} from 'pimfront/product-grid/domain/model/filter/filter';

export const filterAdded = (filter: NormalizedFilter) => {
  return {type: 'FILTER_ADDED', filter};
};
