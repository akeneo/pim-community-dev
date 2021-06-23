import {TableRow} from '../../../src/models/TableValue';
import {TableValueWithId} from '../../../src/product/TableFieldApp';

export const getTableValueSelectRow: () => TableRow = () => {
  return {
    ingredient: 'sugar',
    quantity: 100,
    part: '10g',
    is_allergenic: true,
  };
};

export const getTableValueWithId: () => TableValueWithId = () => {
  return [
    {'unique id': 'uniqueidsugar', ingredient: 'sugar', quantity: 100, part: '10g', is_allergenic: true},
    {'unique id': 'uniqueidsalt', ingredient: 'salt', part: '66g', is_allergenic: false},
    {'unique id': 'uniqueidcaramel', ingredient: 'caramel'},
  ] as TableValueWithId;
};
