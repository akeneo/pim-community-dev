import {DataType, TableConfiguration} from '../../src';

export const getComplexTableConfiguration: (firstColumnType?: DataType) => TableConfiguration = (
  firstColumnType = 'select'
) => {
  const firstColumn =
    firstColumnType === 'select'
      ? {data_type: 'select', code: 'ingredient', labels: {en_US: 'Ingredients'}, validations: {}}
      : {
          data_type: 'reference_entity',
          code: 'city',
          labels: {en_US: 'City'},
          validations: {},
          reference_entity_identifier: 'city',
        };
  return [
    firstColumn,
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
    {
      data_type: 'measurement',
      code: 'ElectricCharge',
      labels: {
        en_US: 'Electric Charge',
      },
      validations: {},
      measurement_family_code: 'ElectricCharge',
      measurement_default_unit_code: 'MILLIAMPEREHOUR',
    },
  ] as TableConfiguration;
};

export const getSimpleTableConfiguration: () => TableConfiguration = () => {
  return [
    {data_type: 'select', code: 'ingredient', labels: {en_US: 'Ingredients'}, validations: {}},
  ] as TableConfiguration;
};
