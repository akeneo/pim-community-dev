import {CellMatchersMapping} from '../../src';
import {default as TableMatcherSelect} from '../../src/product/CellMatchers/SelectMatcher';
import {default as TableMatcherNumber} from '../../src/product/CellMatchers/NumberMatcher';
import {default as TableMatcherText} from '../../src/product/CellMatchers/TextMatcher';
import {default as TableMatcherBoolean} from '../../src/product/CellMatchers/BooleanMatcher';
import {default as TableMatcherRecord} from '../../src/product/CellMatchers/RecordMatcher';

export const defaultCellMatchersMapping: CellMatchersMapping = {
  select: {default: TableMatcherSelect},
  number: {default: TableMatcherNumber},
  text: {default: TableMatcherText},
  boolean: {default: TableMatcherBoolean},
  record: {default: TableMatcherRecord},
};
