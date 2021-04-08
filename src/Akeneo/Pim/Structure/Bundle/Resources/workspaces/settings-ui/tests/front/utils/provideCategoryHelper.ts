import {Category} from '@akeneo-pim-community/settings-ui';

const aCategory = (code: string = 'a_category', label?: string, id: number = 1234): Category => ({
  id,
  code,
  label: label || `Category ${code}`,
});

const aListOfCategories = (codes: string[]): Category[] => {
  return codes.map((code, index) => aCategory(code, undefined, index));
};

export {aCategory, aListOfCategories};
