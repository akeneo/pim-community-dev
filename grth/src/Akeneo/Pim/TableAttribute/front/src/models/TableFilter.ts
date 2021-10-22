import {ColumnCode, ColumnDefinition, SelectOption, SelectOptionCode} from './TableConfiguration';

export type FilterOperator =
  | 'STARTS WITH'
  | 'ENDS WITH'
  | 'CONTAINS'
  | 'DOES NOT CONTAIN'
  | '='
  | '!='
  | 'EMPTY'
  | 'NOT EMPTY'
  | '>'
  | '>='
  | '<'
  | '<='
  | 'IN'
  | 'NOT IN';

export type FilterValue = string | string[] | number | boolean;

export type BackendTableFilterValue =
  | {}
  | {
      row?: SelectOptionCode;
      column: ColumnCode;
      operator: FilterOperator;
      value: FilterValue;
    };

export type PendingBackendTableFilterValue = {
  row?: SelectOptionCode;
  column?: ColumnCode;
  operator?: FilterOperator;
  value?: FilterValue;
};

export type PendingTableFilterValue = {
  row?: SelectOption;
  column?: ColumnDefinition;
  operator?: FilterOperator;
  value?: FilterValue;
};

const isFilterValid: (filter: PendingTableFilterValue) => boolean = filter => {
  return (
    typeof filter.column !== 'undefined' &&
    typeof filter.operator !== 'undefined' &&
    (['EMPTY', 'NOT EMPTY'].includes(filter.operator) ||
      (Array.isArray(filter.value) && filter.value.length > 0) ||
      (!Array.isArray(filter.value) && filter.value !== '' && typeof filter.value !== 'undefined'))
  );
};

export {isFilterValid};
