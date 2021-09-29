import {default as BooleanFilterValue} from '../../src/datagrid/FilterValues/BooleanFilterValue';
import {default as StringFilterValue} from '../../src/datagrid/FilterValues/StringFilterValue';
import {default as NumberFilterValue} from '../../src/datagrid/FilterValues/NumberFilterValue';
import {default as MultiSelectFilterValue} from '../../src/datagrid/FilterValues/MultiSelectFilterValue';
import {default as EmptyFilterValue} from '../../src/datagrid/FilterValues/EmptyFilterValue';
import {FilterValuesMapping} from '../../src/datagrid';

export const defaultFilterValuesMapping: FilterValuesMapping = {
  text: {
    'STARTS WITH': {default: StringFilterValue},
    'ENDS WITH': {default: StringFilterValue},
    CONTAINS: {default: StringFilterValue},
    'DOES NOT CONTAIN': {default: StringFilterValue},
    '=': {default: StringFilterValue},
    '!=': {default: StringFilterValue},
    EMPTY: {default: EmptyFilterValue},
    'NOT EMPTY': {default: EmptyFilterValue},
  },
  number: {
    '>': {default: NumberFilterValue},
    '>=': {default: NumberFilterValue},
    '<': {default: NumberFilterValue},
    '<=': {default: NumberFilterValue},
    '=': {default: NumberFilterValue},
    '!=': {default: NumberFilterValue},
    EMPTY: {default: EmptyFilterValue},
    'NOT EMPTY': {default: EmptyFilterValue},
  },
  boolean: {
    '=': {default: BooleanFilterValue},
    '!=': {default: BooleanFilterValue},
    EMPTY: {default: BooleanFilterValue},
    'NOT EMPTY': {default: BooleanFilterValue},
  },
  select: {
    EMPTY: {default: EmptyFilterValue},
    'NOT EMPTY': {default: EmptyFilterValue},
    IN: {default: MultiSelectFilterValue},
    'NOT IN': {default: MultiSelectFilterValue},
  },
};
