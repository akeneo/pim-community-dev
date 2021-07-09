import {Router} from '@akeneo-pim-community/shared';
import {ColumnCode, SelectOption} from '../../models/TableConfiguration';

const defaultSelectOptions = [
  {code: 'salt', labels: {en_US: 'Salt', de_DE: 'Achtzergüntlich'}},
  {code: 'pepper', labels: {en_US: 'Pepper'}},
  {code: 'eggs', labels: {}},
  {code: 'sugar', labels: {en_US: 'Sugar'}},
] as SelectOption[];

const getSelectOptions: (_router: Router, attributeCode: string) => SelectOption[] = (_router, attributeCode) => {
  if (attributeCode === 'attribute_with_a_lot_of_options') {
    const selectOptions = [];
    for (let i = 0; i < 50; i++) {
      selectOptions.push({code: `code${i}`, labels: {en_US: `label${i}`}});
    }
    return selectOptions;
  }

  return defaultSelectOptions;
};

const fetchSelectOptions = async (
  router: Router,
  attributeCode: string,
  columnCode: ColumnCode
): Promise<SelectOption[] | undefined> => {
  if (columnCode === 'ingredient') {
    return new Promise(resolve => resolve(getSelectOptions(router, attributeCode)));
  } else {
    return new Promise(resolve => resolve(undefined));
  }
};

export {fetchSelectOptions, getSelectOptions, defaultSelectOptions};
