import {
  default as BooleanFilterValue,
  useValueRenderer as BooleanUseValueRenderer,
} from '../../src/datagrid/FilterValues/BooleanFilterValue';
import {
  default as StringFilterValue,
  useValueRenderer as StringUseValueRenderer,
} from '../../src/datagrid/FilterValues/StringFilterValue';
import {
  default as NumberFilterValue,
  useValueRenderer as NumberUseValueRenderer,
} from '../../src/datagrid/FilterValues/NumberFilterValue';
import {
  default as MultiSelectFilterValue,
  useValueRenderer as MultiSelectUseValueRenderer,
} from '../../src/datagrid/FilterValues/MultiSelectFilterValue';
import {
  default as EmptyFilterValue,
  useValueRenderer as EmptyUseValueRenderer,
} from '../../src/datagrid/FilterValues/EmptyFilterValue';
import {FilterValuesMapping} from '../../src/datagrid';

export const defaultFilterValuesMapping: FilterValuesMapping = {
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
};
