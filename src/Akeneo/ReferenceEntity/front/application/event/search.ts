import {Column} from 'akeneoreferenceentity/application/reducer/grid';

export const startLoading = () => {
  return {type: 'GRID_START_LOADING_RESULTS'};
};

export const stopLoading = () => {
  return {type: 'GRID_STOP_LOADING_RESULTS'};
};

export const goNextPage = () => {
  return {type: 'GRID_GO_NEXT_PAGE'};
};

export const goFirstPage = () => {
  return {type: 'GRID_GO_FIRST_PAGE'};
};

export const updateFilter = (field: string, operator: string, value: string) => {
  return {type: 'GRID_UPDATE_FILTER', field, operator, value};
};

export const updateColumns = (columns: Column[]) => {
  return {type: 'GRID_UPDATE_COLUMNS', columns};
};
