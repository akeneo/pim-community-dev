import {BackendCategoryTree, Category, CategoryTree} from '@akeneo-pim-community/settings-ui';

const aCategory = (code: string = 'a_category', label?: string, id: number = 1234): Category => ({
  id,
  code,
  label: label || `Category ${code}`,
});

const aListOfCategories = (codes: string[]): Category[] => {
  return codes.map((code, index) => aCategory(code, undefined, index));
};

const aCategoryTree = (
  code: string,
  children: string[],
  isRoot: boolean = true,
  id: number = 1234
): CategoryTree => {
  const root = aCategory(code, `[${code}]`, id);
  return {
    ...root,
    isRoot,
    children: children.map((child, index) => aCategoryTree(child, [], false, index)),
  };
};

const aBackendCategoryTree = (
  code: string,
  children: string[],
  isRoot: boolean = true,
  id: number = 1234
): BackendCategoryTree => {
  return {
    attr: {
      id: `node_${id}`,
      'data-code': code,
    },
    data: `[${code}]`,
    state: isRoot ? 'closed jstree-root' : children.length > 0 ? 'closed' : 'leaf',
    children: children.map((child, index) => aBackendCategoryTree(child, [], false, index)),
  };
};

export {aCategory, aListOfCategories, aCategoryTree, aBackendCategoryTree};
