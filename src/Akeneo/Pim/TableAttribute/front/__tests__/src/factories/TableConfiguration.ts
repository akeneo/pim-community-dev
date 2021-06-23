import { TableConfiguration } from "../../../src/models/TableConfiguration";

export const getComplexTableConfiguration: () => TableConfiguration = () => {
  return [
    {data_type: 'select', code: 'ingredients', labels: {en_US: 'Ingredients'}, validations: {}},
    {data_type: 'number', code: 'quantity', labels: {en_US: 'Quantity'}, validations: {}},
    {data_type: 'boolean', code: 'is_allergenic', labels: {en_US: 'Is allergenic'}, validations: {}},
    {data_type: 'text', code: 'part', labels: {en_US: 'For 1 part'}, validations: {}},
  ] as TableConfiguration;
}

export const getSimpleTableConfiguration: () => TableConfiguration = () => {
  return [
    {data_type: 'text', code: 'ingredients', labels: {en_US: 'Ingredients'}, validations: {}},
  ] as TableConfiguration;
}
