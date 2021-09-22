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

const fetchSelectOptions = async (
  _router: Router,
  _attributeCode: string,
  columnCode: ColumnCode
): Promise<SelectOption[] | undefined> => {
  const options: {[columnCode: string]: SelectOption[] | undefined} = {
    ingredient: ingredientsSelectOptions,
    nutrition_score: nutritionScoreSelectOptions,
    no_options: [],
    unknown: undefined,
  };

  return new Promise(resolve => resolve(options[columnCode]));
};

const SelectOptionFetcher = {
  fetchFromColumn: fetchSelectOptions,
};

export {SelectOptionFetcher, ingredientsSelectOptions};
