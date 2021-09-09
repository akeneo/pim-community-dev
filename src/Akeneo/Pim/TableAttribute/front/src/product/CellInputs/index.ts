import React from 'react';
import {TableRowWithId} from '../TableFieldApp';
import {ColumnDefinition, TableAttribute} from '../../models';
import {Translate} from '@akeneo-pim-community/shared';

export type CellInput = React.FC<{
  row: TableRowWithId;
  columnDefinition: ColumnDefinition;
  onChange: (value: any) => void;
  inError: boolean;
  translate: Translate;
  attribute: TableAttribute;
  highlighted: boolean;
}>;

export type CellInputsMapping = {
  [data_type: string]: {
    default: CellInput;
  };
};
