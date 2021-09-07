import {ColumnDefinitionWithId} from '../../../src/attribute';
import {SelectColumnDefinition} from '../../../src/models';

export const getSelectColumnDefinitionWithId: () => ColumnDefinitionWithId = () => {
  return {...getSelectColumnDefinition(), id: 'uniqueidingredient'};
};

export const getSelectColumnDefinition: () => SelectColumnDefinition = () => {
  return {
    code: 'ingredient',
    validations: {},
    data_type: 'select',
    labels: {},
  };
};

export const getTextColumnDefinitionWithId: () => ColumnDefinitionWithId = () => {
  return {
    code: 'part',
    validations: {},
    data_type: 'text',
    labels: {},
    id: 'uniqueidpart',
  } as ColumnDefinitionWithId;
};

export const getNumberColumnDefinitionWithId: () => ColumnDefinitionWithId = () => {
  return {
    code: 'quantity',
    validations: {},
    data_type: 'number',
    labels: {},
    id: 'uniqueidquantity',
  } as ColumnDefinitionWithId;
};
