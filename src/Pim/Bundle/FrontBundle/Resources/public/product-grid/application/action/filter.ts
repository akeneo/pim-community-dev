import {updateResults} from 'pimfront/product-grid/application/action/search';
import {filterAdded} from 'pimfront/product-grid/domain/event/filter';
import filterModelProvider from 'pimfront/product-grid/application/configuration/filter-model';
import Filter from 'pimfront/product-grid/domain/model/filter/filter';

export const addFilter = (filterCode: string) => async (dispatch: any, getState: any): Promise<void> => {
  const filter: Filter = await filterModelProvider.getEmptyFilter(filterCode);

  dispatch(filterAdded(filter.normalize()));

  return dispatch(updateResults());
};
