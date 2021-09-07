import {TableConfiguration} from '../../../src/models';

export const getComplexTableConfiguration: () => TableConfiguration = () => {
  return [
    {data_type: 'select', code: 'ingredient', labels: {en_US: 'Ingredients'}, validations: {}},
    {data_type: 'number', code: 'quantity', labels: {en_US: 'Quantity'}, validations: {}},
    {data_type: 'boolean', code: 'is_allergenic', labels: {en_US: 'Is allergenic'}, validations: {}},
    {data_type: 'text', code: 'part', labels: {en_US: 'For 1 part'}, validations: {}},
    {
      data_type: 'select',
      code: 'nutrition_score',
      labels: {en_US: 'Nutrition score'},
      validations: {},
      options: [{code: 'A'}, {code: 'B'}, {code: 'C'}],
    },
  ] as TableConfiguration;
};

export const getSimpleTableConfiguration: () => TableConfiguration = () => {
  return [
    {data_type: 'select', code: 'ingredient', labels: {en_US: 'Ingredients'}, validations: {}},
  ] as TableConfiguration;
};
