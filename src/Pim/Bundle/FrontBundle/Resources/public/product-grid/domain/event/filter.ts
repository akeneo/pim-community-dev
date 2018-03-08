import Filter from 'pimfront/product-grid/domain/model/filter/filter';

export const filterAdded = (filters: Filter[]) => {
  return {type: 'FILTER_ADDED', filters};
};
