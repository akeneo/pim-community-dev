import React from 'react';
import {TableRowWithId} from '../TableFieldApp';
import {ColumnDefinition, TableAttribute} from '../../models';

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
  [data_type: string]: {
    default: CellInput;
  };
};
