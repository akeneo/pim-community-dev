import {TableRow, TableValueWithId} from '../../src';

export const getTableValueSelectRow: () => TableRow = () => {
  return {
    ingredient: 'sugar',
    quantity: 100,
    part: '10g',
    is_allergenic: true,
    nutrition_score: 'A',
  };
};

export const getTableValueWithId: () => TableValueWithId = () => {
  return [
    {
      'unique id': 'uniqueidsugar',
      ingredient: 'sugar',
      quantity: 100,
      part: '10g',
      is_allergenic: true,
      nutrition_score: 'A',
    },
    {'unique id': 'uniqueidsalt', ingredient: 'salt', part: '66g', is_allergenic: false, nutrition_score: 'B'},
    {'unique id': 'uniqueidcaramel', ingredient: 'caramel'},
  ] as TableValueWithId;
};
