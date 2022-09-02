import {ColumnCode, SelectOptionCode} from './TableConfiguration';
import {RecordCode} from './ReferenceEntityRecord';
import {MeasurementValue} from './MeasurementFamily';

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

export type FilterValue = string | number | boolean | RecordCode[] | SelectOptionCode[] | MeasurementValue;

export type BackendTableFilterValue = {
  row?: SelectOptionCode | RecordCode | null;
  column?: ColumnCode;
  operator?: FilterOperator;
  value?: FilterValue;
};

export type PendingBackendTableFilterValue = {
  row?: SelectOptionCode | RecordCode | null;
  column?: ColumnCode;
  operator?: FilterOperator;
  value?: FilterValue;
};

export type NotEmptyTableFilterValue = {
  operator?: 'NOT EMPTY';
};

const isFilterValid: (filter: PendingBackendTableFilterValue) => boolean = filter => {
  return (
    typeof filter.row !== 'undefined' &&
    typeof filter.column !== 'undefined' &&
    typeof filter.operator !== 'undefined' &&
    (['EMPTY', 'NOT EMPTY'].includes(filter.operator) ||
      (Array.isArray(filter.value) && filter.value.length > 0) ||
      (!Array.isArray(filter.value) && filter.value !== '' && typeof filter.value !== 'undefined') ||
      (typeof filter.value === 'object' &&
        !Array.isArray(filter.value) &&
        Object.prototype.hasOwnProperty.call(filter.value, 'amount') &&
        Object.prototype.hasOwnProperty.call(filter.value, 'unit') &&
        (filter.value as MeasurementValue).amount !== ''))
  );
};

export {isFilterValid};
