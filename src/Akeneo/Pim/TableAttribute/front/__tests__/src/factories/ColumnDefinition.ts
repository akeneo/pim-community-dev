import {ColumnDefinitionWithId} from '../../../src/attribute/TableStructureApp';

export const getSelectColumnDefinitionWithId: () => ColumnDefinitionWithId = () => {
  return {
    code: 'ingredient',
    validations: {},
    data_type: 'select',
    labels: {},
    id: 'ingredientid',
  } as ColumnDefinitionWithId;
};

export const getTextColumnDefinitionWithId: () => ColumnDefinitionWithId = () => {
  return {
    code: 'aqr',
    validations: {},
    data_type: 'text',
    labels: {},
    id: 'aqrid',
  } as ColumnDefinitionWithId;
};

export const getNumberColumnDefinitionWithId: () => ColumnDefinitionWithId = () => {
  return {
    code: 'quantity',
    validations: {},
    data_type: 'number',
    labels: {},
    id: 'quantityid',
  } as ColumnDefinitionWithId;
};
