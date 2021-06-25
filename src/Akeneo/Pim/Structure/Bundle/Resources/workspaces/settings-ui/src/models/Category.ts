import {LabelCollection} from '@akeneo-pim-community/shared';
import {TreeNode} from './Tree';

export type Category = {
  id: number;
  code: string;
  labels: LabelCollection;
  root: Category | null;
};

export type EditableCategoryProperties = {
  labels: LabelCollection;
};

export type CategoryPermissions = {
  view: number[];
  edit: number[];
  own: number[];
  apply_on_children: '0' | '1';
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
  productsNumber?: number;
};

export type FormField = {
  value: string;
  fullName: string;
  label: string;
};

export type HiddenFormField = {
  value: string;
  fullName: string;
};

export type FormChoiceField = {
  value: string[];
  fullName: string;
  choices: {
    value: string;
    label: string;
  }[];
};

export type EditCategoryForm = {
  label: {[locale: string]: FormField};
  _token: HiddenFormField;
  permissions?: {
    view: FormChoiceField;
    edit: FormChoiceField;
    own: FormChoiceField;
    apply_on_children: HiddenFormField;
  };
  errors: string[];
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
    childrenIds: Array.isArray(categoryTree.children) ? categoryTree.children.map(child => child.id) : [],
    data: categoryTree,
    parentId: parent,
    type: categoryTree.isRoot ? 'root' : categoryTree.isLeaf ? 'leaf' : 'node',
    childrenStatus: categoryTree.children && categoryTree.children.length > 0 ? 'loaded' : 'idle',
  };
};

export {convertToCategoryTree, buildTreeNodeFromCategoryTree};
