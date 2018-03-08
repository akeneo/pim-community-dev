import {updateResults} from 'pimfront/product-grid/application/action/search';

import {filterAdded} from 'pimfront/product-grid/domain/event/filter';

import filtersConfiguration from 'pimfront/product-grid/application/configuration/filters';

export const addFilters = (filterCodes: string[]) => async (dispatch: any, getState: any): Promise<void> => {
  const filters = await filtersConfiguration.getEmptyFiltersFromCodes(filterCodes);

  dispatch(filterAdded(filters));

  return dispatch(updateResults());
};
