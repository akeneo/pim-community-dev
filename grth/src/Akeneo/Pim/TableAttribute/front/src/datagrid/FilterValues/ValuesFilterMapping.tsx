import {default as BooleanFilterValue, useValueRenderer as BooleanUseValueRenderer} from './BooleanFilterValue';
import {default as StringFilterValue, useValueRenderer as StringUseValueRenderer} from './StringFilterValue';
import {default as NumberFilterValue, useValueRenderer as NumberUseValueRenderer} from './NumberFilterValue';
import {
  default as MeasurementFilterValue,
  useValueRenderer as MeasurementUseValueRenderer,
} from './MeasurementFilterValue';
import {
  default as MultiSelectFilterValue,
  useValueRenderer as MultiSelectUseValueRenderer,
} from './MultiSelectFilterValue';
import {default as EmptyFilterValue, useValueRenderer as EmptyUseValueRenderer} from './EmptyFilterValue';
import {ColumnCode, FilterValue} from '../../models';
import {
  default as MultiSelectReferenceEntityFilterValue,
  useValueRenderer as RecordUseValueRenderer,
} from './MultiSelectRecordsFilterValue';

type DatagridTableFilterValueProps = {
  value?: FilterValue;
  onChange: (value?: FilterValue) => void;
  columnCode: ColumnCode;
};

export type TableFilterValueRenderer = React.FC<DatagridTableFilterValueProps>;
export type FilteredValueRenderer = (value?: FilterValue, columnCode?: ColumnCode) => string | null;

export type FilterValuesMapping = {
  [data_type: string]: {
    [operator: string]: {
      default: TableFilterValueRenderer;
      useValueRenderer: FilteredValueRenderer;
    };
  };
};

const ValuesFilterMapping: FilterValuesMapping = {
  text: {
    'STARTS WITH': {default: StringFilterValue, useValueRenderer: StringUseValueRenderer},
    'ENDS WITH': {default: StringFilterValue, useValueRenderer: StringUseValueRenderer},
    CONTAINS: {default: StringFilterValue, useValueRenderer: StringUseValueRenderer},
    'DOES NOT CONTAIN': {default: StringFilterValue, useValueRenderer: StringUseValueRenderer},
    '=': {default: StringFilterValue, useValueRenderer: StringUseValueRenderer},
    '!=': {default: StringFilterValue, useValueRenderer: StringUseValueRenderer},
    EMPTY: {default: EmptyFilterValue, useValueRenderer: EmptyUseValueRenderer},
    'NOT EMPTY': {default: EmptyFilterValue, useValueRenderer: EmptyUseValueRenderer},
  },
  number: {
    '>': {default: NumberFilterValue, useValueRenderer: NumberUseValueRenderer},
    '>=': {default: NumberFilterValue, useValueRenderer: NumberUseValueRenderer},
    '<': {default: NumberFilterValue, useValueRenderer: NumberUseValueRenderer},
    '<=': {default: NumberFilterValue, useValueRenderer: NumberUseValueRenderer},
    '=': {default: NumberFilterValue, useValueRenderer: NumberUseValueRenderer},
    '!=': {default: NumberFilterValue, useValueRenderer: NumberUseValueRenderer},
    EMPTY: {default: EmptyFilterValue, useValueRenderer: EmptyUseValueRenderer},
    'NOT EMPTY': {default: EmptyFilterValue, useValueRenderer: EmptyUseValueRenderer},
  },
  boolean: {
    '=': {default: BooleanFilterValue, useValueRenderer: BooleanUseValueRenderer},
    '!=': {default: BooleanFilterValue, useValueRenderer: BooleanUseValueRenderer},
    EMPTY: {default: EmptyFilterValue, useValueRenderer: EmptyUseValueRenderer},
    'NOT EMPTY': {default: EmptyFilterValue, useValueRenderer: EmptyUseValueRenderer},
  },
  select: {
    EMPTY: {default: EmptyFilterValue, useValueRenderer: EmptyUseValueRenderer},
    'NOT EMPTY': {default: EmptyFilterValue, useValueRenderer: EmptyUseValueRenderer},
    IN: {default: MultiSelectFilterValue, useValueRenderer: MultiSelectUseValueRenderer},
    'NOT IN': {default: MultiSelectFilterValue, useValueRenderer: MultiSelectUseValueRenderer},
  },
  reference_entity: {
    EMPTY: {default: EmptyFilterValue, useValueRenderer: EmptyUseValueRenderer},
    'NOT EMPTY': {default: EmptyFilterValue, useValueRenderer: EmptyUseValueRenderer},
    IN: {default: MultiSelectReferenceEntityFilterValue, useValueRenderer: RecordUseValueRenderer},
    'NOT IN': {default: MultiSelectReferenceEntityFilterValue, useValueRenderer: RecordUseValueRenderer},
  },
  measurement: {
    '>': {default: MeasurementFilterValue, useValueRenderer: MeasurementUseValueRenderer},
    '>=': {default: MeasurementFilterValue, useValueRenderer: MeasurementUseValueRenderer},
    '<': {default: MeasurementFilterValue, useValueRenderer: MeasurementUseValueRenderer},
    '<=': {default: MeasurementFilterValue, useValueRenderer: MeasurementUseValueRenderer},
    '=': {default: MeasurementFilterValue, useValueRenderer: MeasurementUseValueRenderer},
    '!=': {default: MeasurementFilterValue, useValueRenderer: MeasurementUseValueRenderer},
    EMPTY: {default: EmptyFilterValue, useValueRenderer: EmptyUseValueRenderer},
    'NOT EMPTY': {default: EmptyFilterValue, useValueRenderer: EmptyUseValueRenderer},
  },
};

export {ValuesFilterMapping};
