import {ColumnCode, ColumnDefinition, SelectOption, SelectOptionCode} from './TableConfiguration';
import {RecordCode} from './ReferenceEntityRecord';

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

export type FilterValue = string | number | boolean | RecordCode[] | SelectOptionCode[];

export type BackendTableFilterValue =
  | {}
  | {
      row?: SelectOptionCode;
      column: ColumnCode;
      operator: FilterOperator;
      value: FilterValue;
    };

export type PendingBackendTableFilterValue = {
  row?: SelectOptionCode | RecordCode;
  column?: ColumnCode;
  operator?: FilterOperator;
  value?: FilterValue;
};

export type PendingTableFilterValue = {
  row?: SelectOption | null;
  column?: ColumnDefinition;
  operator?: FilterOperator;
  value?: FilterValue;
};

export type NotEmptyTableFilterValue = {
  operator?: 'NOT EMPTY';
};

const isFilterValid: (filter: PendingTableFilterValue) => boolean = filter => {
  return (
    typeof filter.row !== 'undefined' &&
    typeof filter.column !== 'undefined' &&
    typeof filter.operator !== 'undefined' &&
    (['EMPTY', 'NOT EMPTY'].includes(filter.operator) ||
      (Array.isArray(filter.value) && filter.value.length > 0) ||
      (!Array.isArray(filter.value) && filter.value !== '' && typeof filter.value !== 'undefined'))
  );
};

export {isFilterValid};
