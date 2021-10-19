import {ColumnCode, ColumnDefinition, SelectOption, SelectOptionCode} from './TableConfiguration';

export type BackendTableFilterValue = {
  row?: SelectOptionCode;
  column: ColumnCode;
  operator: string;
  value: any;
};

export type PendingBackendTableFilterValue = {
  row?: SelectOptionCode;
  column?: ColumnCode;
  operator?: string;
  value?: any;
};

export type PendingTableFilterValue = {
  row?: SelectOption;
  column?: ColumnDefinition;
  operator?: string;
  value?: any;
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
