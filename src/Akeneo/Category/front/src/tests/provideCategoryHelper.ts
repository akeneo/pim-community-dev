import {LabelCollection} from '@akeneo-pim-community/shared';
import {BackendCategoryTree, Category, CategoryTreeModel} from '../feature/models';

const aCategory = (
  code: string = 'a_category',
  labels?: LabelCollection,
  id: number = 1234,
  root: Category | null = null
): Category => ({
  id,
  code,
  labels: labels || {
    en_US: `[${code}]`,
  },
  root,
});

const aListOfCategories = (codes: string[]): Category[] => {
  return codes.map((code, index) => aCategory(code, undefined, index));
};

const aCategoryTree = (
  code: string,
  children: string[],
  isRoot: boolean = true,
  isLeaf: boolean = false,
  id: number = 1234,
  productsNumber?: number,
  templateLabel?: string
): CategoryTreeModel => {
  return {
    id,
    code,
    label: `[${code}]`,
    isRoot,
    isLeaf,
    children: children.map((child, index) =>
      aCategoryTree(
        child,
        [],
        false,
        // even ids are leaves
        (index + 1) % 2 === 0,
        index + 1,
        productsNumber !== undefined ? productsNumber % children.length : undefined
      )
    ),
    productsNumber,
    templateLabel,
  };
};

const aCategoryTreeWithChildren = (
  code: string,
  children: CategoryTreeModel[],
  isRoot: boolean = true,
  isLeaf: boolean = false,
  id: number = 1234,
  productsNumber?: number,
  templateLabel?: string
): CategoryTreeModel => {
  return {
    id,
    code,
    label: `[${code}]`,
    isRoot,
    isLeaf,
    children,
    productsNumber,
    templateLabel,
  };
};

const aBackendCategoryTree = (
  code: string,
  children: string[],
  isRoot: boolean = true,
  id: number = 1234,
  isLeaf: boolean = false
): BackendCategoryTree => {
  return {
    attr: {
      id: `node_${id}`,
      'data-code': code,
    },
    data: `[${code}]`,
    state: isRoot ? 'closed jstree-root' : isLeaf ? 'leaf' : 'closed',
    children: children.map((child, index) => aBackendCategoryTree(child, [], false, index + 1, (index + 1) % 2 === 0)),
  };
};

export {aCategory, aListOfCategories, aCategoryTree, aBackendCategoryTree, aCategoryTreeWithChildren};
