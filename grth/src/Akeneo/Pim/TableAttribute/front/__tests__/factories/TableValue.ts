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

export const getTableValueWithId: (firstColumnType?: 'select' | 'reference_entity') => TableValueWithId = (
  firstColumnType = 'select'
) => {
  return firstColumnType === 'select'
    ? ([
        {
          'unique id': 'uniqueidsugar',
          ingredient: 'sugar',
          quantity: 100,
          part: '10g',
          is_allergenic: true,
          nutrition_score: 'A',
          ElectricCharge: {amount: 10, unit: 'MILLICOULOMB'},
        },
        {'unique id': 'uniqueidsalt', ingredient: 'salt', part: '66g', is_allergenic: false, nutrition_score: 'B'},
        {'unique id': 'uniqueidcaramel', ingredient: 'caramel'},
      ] as TableValueWithId)
    : ([
        {
          'unique id': 'uniqueidvannes',
          city: 'vannes00bcf56a_2aa9_47c5_ac90_a973460b18a3',
          quantity: 100,
          part: '10g',
          is_allergenic: true,
          nutrition_score: 'A',
          ElectricCharge: {amount: 10, unit: 'MILLICOULOMB'},
        },
        {
          'unique id': 'uniqueidnantes',
          city: 'nantes00e3cffd_f60e_4a51_925b_d2952bd947e1',
          part: '66g',
          is_allergenic: false,
          nutrition_score: 'B',
        },
        {'unique id': 'uniqueidbrest', city: 'brest00bcf56a_2aa9_47c5_ac90_a973460b18a3'},
      ] as TableValueWithId);
};
