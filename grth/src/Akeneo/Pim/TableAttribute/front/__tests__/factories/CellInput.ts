import {CellInputsMapping} from '../../src';
import {default as TableInputSelect} from '../../src/product/CellInputs/SelectInput';
import {default as TableInputNumber} from '../../src/product/CellInputs/NumberInput';
import {default as TableInputText} from '../../src/product/CellInputs/TextInput';
import {default as TableInputBoolean} from '../../src/product/CellInputs/BooleanInput';

export const defaultCellInputsMapping: CellInputsMapping = {
  select: {default: TableInputSelect},
  number: {default: TableInputNumber},
  text: {default: TableInputText},
  boolean: {default: TableInputBoolean},
};
