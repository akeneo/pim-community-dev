import {ColumnDefinitionWithId} from '../../../src/attribute/TableStructureApp';

export const getSelectColumnDefinitionWithId: () => ColumnDefinitionWithId = () => {
  return {
    code: 'ingredient',
    validations: {},
    data_type: 'select',
    labels: {},
    id: 'uniqueidingredient',
  } as ColumnDefinitionWithId;
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
