import {LabelCollection} from '@akeneo-pim-community/shared';
import {TreeNode} from './Tree';

export type Category = {
  id: number;
  code: string;
  labels: LabelCollection;
  parent: string;
  root: Category | null;
};

export type BackendCategoryTree = {
  attr: {
    id: string; // format: node_([0-9]+)
    'data-code': string;
  };
  data: string;
  state: 'leaf' | 'closed' | 'closed jstree-root';
  children?: BackendCategoryTree[];
};

export type CategoryTreeModel = {
  id: number;
  code: string;
  label: string;
  isRoot: boolean;
  isLeaf: boolean;
  children?: CategoryTreeModel[];
};

const convertToCategoryTree = (tree: BackendCategoryTree): CategoryTreeModel => {
  return {
    id: parseInt(tree.attr.id.substring(5)), // remove the "node_" prefix and returns the number
    code: tree.attr['data-code'],
    label: tree.data,
    isRoot: tree.state.match(/root/) !== null,
    isLeaf: tree.state.match(/leaf/) !== null,
    children: tree.children !== undefined ? tree.children.map(subtree => convertToCategoryTree(subtree)) : [],
  };
};

const buildTreeNodeFromCategoryTree = (
  categoryTree: CategoryTreeModel,
  parent: number | null = null
): TreeNode<CategoryTreeModel> => {
  return {
    identifier: categoryTree.id,
    label: categoryTree.label,
    children: Array.isArray(categoryTree.children) ? categoryTree.children.map(child => child.id) : [],
    data: categoryTree,
    parent,
    selected: false,
    type: categoryTree.isRoot ? 'root' : categoryTree.isLeaf ? 'leaf' : 'node',
  };
};

export {convertToCategoryTree, buildTreeNodeFromCategoryTree};
