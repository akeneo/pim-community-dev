import {CategoryTree} from '@akeneo-pim-community/settings-ui';

const aCategoryTree = (code: string = 'a_root_category', label?: string, id: number = 1234): CategoryTree => ({
  id,
  code,
  label: label || `Category tree ${code}`,
});

const aListOfCategoryTrees = (codes: string[]): CategoryTree[] => {
  return codes.map((code, index) => aCategoryTree(code, undefined, index));
};

export {aCategoryTree, aListOfCategoryTrees};
