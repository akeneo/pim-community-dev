import {getFilter} from 'akeneoassetmanager/tools/filter';
import {Filter} from 'akeneoassetmanager/application/reducer/grid';
import {CompletenessValue} from 'akeneoassetmanager/application/component/asset/list/completeness-filter';

export const getCompletenessFilter = (filters: Filter[]): CompletenessValue => {
  const filter = getFilter(filters, 'complete');
  const value = undefined !== filter ? filter.value : null;

  switch (value) {
    case true:
      return CompletenessValue.Yes;
    case false:
      return CompletenessValue.No;
    default:
      return CompletenessValue.All;
  }
};

export const updateCompletenessFilter = (filters: Filter[], value: CompletenessValue): Filter[] => {
  switch (value) {
    case CompletenessValue.All:
      return filters.filter((filter: Filter) => filter.field !== 'complete');
    case CompletenessValue.Yes:
      return [
        ...filters.filter((filter: Filter) => filter.field !== 'complete'),
        {field: 'complete', operator: '=', value: true, context: null},
      ];
    case CompletenessValue.No:
      return [
        ...filters.filter((filter: Filter) => filter.field !== 'complete'),
        {field: 'complete', operator: '=', value: false, context: null},
      ];
  }
};
