import {isEqual, sortBy} from 'lodash';
import {LabelCollection, LocaleCode} from '@akeneo-pim-community/shared';
import {TreeNode} from './Tree';
import {CompositeKey, CompositeKeyWithoutLocale} from './CompositeKey';

export type Category = {
  id: number;
  code: string;
  labels: LabelCollection;
  root: Category | null;
};

export type EnrichCategory = {
  id: number;
  properties: CategoryProperties;
  attributes: CategoryAttributes;
  permissions: CategoryPermissions;
};

export type CategoryProperties = {
  code: string;
  labels: LabelCollection;
};

export type CategoryPermissions = {
  view: number[];
  edit: number[];
  own: number[];
};

interface CategoryAttributes {
  [key: CompositeKey]: CategoryAttributeValueWrapper;
}

export interface CategoryAttributeValueWrapper {
  data: CategoryAttributeValueData;
  locale: LocaleCode | null;
  attribute_code: CompositeKeyWithoutLocale;
}

type CategoryTextAttributeValueData = string;
export interface CategoryImageAttributeValueData {
  size: number;
  file_path: string;
  mime_type: string;
  extension: string;
  original_filename: string;
}

export type CategoryAttributeValueData = CategoryTextAttributeValueData | CategoryImageAttributeValueData;

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

function labelsAreEqual(l1: LabelCollection, l2: LabelCollection): boolean {
  // maybe too strict of simplistic, to adjust
  return isEqual(l1, l2);
}

function isEqualUnordered(a1: number[], a2: number[]): boolean {
  return isEqual(sortBy(a1), sortBy(a2));
}

function permissionsAreEqual(cp1: CategoryPermissions, cp2: CategoryPermissions): boolean {
  return (
    isEqualUnordered(cp1.view, cp2.view) && isEqualUnordered(cp1.edit, cp2.edit) && isEqualUnordered(cp1.own, cp2.own)
  );
}

function attributesAreEqual(a1: CategoryAttributes, a2: CategoryAttributes): boolean {
  // maybe too strict of simplistic, to adjust
  return isEqual(a1, a2);
}

function propertiesAreEqual(p1: CategoryProperties, p2: CategoryProperties): boolean {
  return p1.code === p2.code && labelsAreEqual(p1.labels, p2.labels);
}

function categoriesAreEqual(c1: EnrichCategory, c2: EnrichCategory): boolean {
  return (
    c1.id === c2.id &&
    propertiesAreEqual(c1.properties, c2.properties) &&
    permissionsAreEqual(c1.permissions, c2.permissions) &&
    attributesAreEqual(c1.attributes, c2.attributes)
  );
}

export {convertToCategoryTree, buildTreeNodeFromCategoryTree, categoriesAreEqual, permissionsAreEqual};
