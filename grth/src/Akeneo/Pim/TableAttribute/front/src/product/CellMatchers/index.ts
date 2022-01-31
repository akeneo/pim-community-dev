import {ColumnCode, TableCell} from '../../models';
import SelectMatcher from './SelectMatcher';
import NumberMatcher from './NumberMatcher';
import TextMatcher from './TextMatcher';
import BooleanMatcher from './BooleanMatcher';
import RecordMatcher from './RecordMatcher';
import MeasurementMatcher from './MeasurementMatcher';

export type CellMatcher = () => (cell: TableCell, searchText: string, columnCode: ColumnCode) => boolean;

export type CellMatchersMapping = {
  [data_type: string]: CellMatcher;
};

const cellMatchers: CellMatchersMapping = {
  select: SelectMatcher,
  number: NumberMatcher,
  text: TextMatcher,
  boolean: BooleanMatcher,
  reference_entity: RecordMatcher,
  measurement: MeasurementMatcher,
};

export {cellMatchers};
