import React from 'react';
import {TableRowWithId} from '../TableFieldApp';
import {ColumnDefinition, TableAttribute} from '../../models';
import SelectInput from './SelectInput';
import NumberInput from './NumberInput';
import TextInput from './TextInput';
import BooleanInput from './BooleanInput';
import RecordInput from './RecordInput';

export type CellInput = React.FC<{
  row: TableRowWithId;
  columnDefinition: ColumnDefinition;
  onChange: (value: any) => void;
  inError: boolean;
  highlighted: boolean;
  attribute: TableAttribute;
  setAttribute: (tableAttribute: TableAttribute) => void;
}>;

export type CellInputsMapping = {
  [data_type: string]: CellInput;
};

const cellInputs: CellInputsMapping = {
  select: SelectInput,
  number: NumberInput,
  text: TextInput,
  boolean: BooleanInput,
  reference_entity: RecordInput,
};

export {cellInputs};
