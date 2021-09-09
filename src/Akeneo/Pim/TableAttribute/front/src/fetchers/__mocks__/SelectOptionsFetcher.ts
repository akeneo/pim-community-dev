import {Router} from '@akeneo-pim-community/shared';
import {ColumnCode, SelectOption} from '../../models';

const ingredientsSelectOptions = [
  {code: 'salt', labels: {en_US: 'Salt', de_DE: 'Achtzergüntlich'}},
  {code: 'pepper', labels: {en_US: 'Pepper'}},
  {code: 'eggs', labels: {}},
  {code: 'sugar', labels: {en_US: 'Sugar'}},
] as SelectOption[];

const nutritionScoreSelectOptions = [
  {code: 'A', labels: {en_US: 'A'}},
  {code: 'B', labels: {en_US: 'B'}},
  {code: 'C', labels: {en_US: 'C'}},
  {code: 'D', labels: {en_US: 'D'}},
  {code: 'E', labels: {en_US: 'E'}},
  {code: 'F', labels: {en_US: 'F'}},
  {code: 'G', labels: {en_US: 'G'}},
  {code: 'H', labels: {en_US: 'H'}},
  {code: 'I', labels: {en_US: 'I'}},
  {code: 'J', labels: {en_US: 'J'}},
  {code: 'K', labels: {en_US: 'K'}},
  {code: 'L', labels: {en_US: 'L'}},
  {code: 'M', labels: {en_US: 'M'}},
  {code: 'N', labels: {en_US: 'N'}},
  {code: 'O', labels: {en_US: 'O'}},
  {code: 'P', labels: {en_US: 'P'}},
  {code: 'Q', labels: {en_US: 'Q'}},
  {code: 'R', labels: {en_US: 'R'}},
  {code: 'S', labels: {en_US: 'S'}},
  {code: 'T', labels: {en_US: 'T'}},
  {code: 'U', labels: {en_US: 'U'}},
];

const getSelectOptions: (_router: Router, attributeCode: string) => SelectOption[] = (_router, attributeCode) => {
  if (attributeCode === 'attribute_with_a_lot_of_options') {
    const selectOptions = [];
    for (let i = 0; i < 50; i++) {
      selectOptions.push({code: `code${i}`, labels: {en_US: `label${i}`}});
    }
    return selectOptions;
  }

  if (attributeCode === 'test_pagination') {
    const selectOptions = [];
    for (let i = 0; i < 21; i++) {
      selectOptions.push({code: `code${i}`, labels: {en_US: `label${i}`}});
    }
    return selectOptions;
  }

  if (attributeCode === 'attribute_without_options') {
    return [];
  }

  return ingredientsSelectOptions;
};

const fetchSelectOptions = async (
  router: Router,
  attributeCode: string,
  columnCode: ColumnCode
): Promise<SelectOption[] | undefined> => {
  if (columnCode === 'ingredient') {
    return new Promise(resolve => resolve(getSelectOptions(router, attributeCode)));
  } else if (columnCode === 'nutrition_score') {
    return new Promise(resolve => resolve(nutritionScoreSelectOptions));
  } else if (columnCode === 'no_options') {
    return new Promise(resolve => resolve([]));
  } else {
    return new Promise(resolve => resolve(undefined));
  }
};

const SelectOptionFetcher = {
  fetchFromColumn: fetchSelectOptions,
};

export {SelectOptionFetcher, ingredientsSelectOptions};
