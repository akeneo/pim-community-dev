import {Router} from '@akeneo-pim-community/shared';
import {ColumnCode, SelectOption} from '../../models';

const ingredientsSelectOptions = [
  {code: 'salt', labels: {en_US: 'Salt', de_DE: 'Achtzergüntlich'}},
  {code: 'pepper', labels: {en_US: 'Pepper'}},
  {code: 'eggs', labels: {}},
  {code: 'sugar', labels: {en_US: 'Sugar'}},
] as SelectOption[];

const nutritionScoreSelectOptions = [
  {code: 'A', labels: {}},
  {code: 'B', labels: {}},
  {code: 'C', labels: {}},
  {code: 'D', labels: {}},
  {code: 'E', labels: {}},
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
  } else {
    return new Promise(resolve => resolve(undefined));
  }
};

const SelectOptionFetcher = {
  fetchFromColumn: fetchSelectOptions,
};

export {SelectOptionFetcher, ingredientsSelectOptions};
