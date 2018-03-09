import {updateResults} from 'pimfront/product-grid/application/action/search';
import {filterAdded} from 'pimfront/product-grid/domain/event/filter';
import filtersConfiguration from 'pimfront/product-grid/application/configuration/filters';
import Filter from 'pimfront/product-grid/domain/model/filter/filter';

export const addFilters = (filterCodes: string[]) => async (dispatch: any, getState: any): Promise<void> => {
  const filters: Filter[] = await filtersConfiguration.getEmptyFilterModelsFromCodes(filterCodes);

  dispatch(filterAdded(filters.map((filter: Filter) => filter.normalize())));

  return dispatch(updateResults());
};
