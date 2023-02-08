import {ChannelCode, LabelCollection, LocaleCode} from '@akeneo-pim-community/shared';
import {TreeNode} from './Tree';
import {CompositeKeyWithoutLocale} from './CompositeKey';
import {CategoryAttributeType} from './Attribute';
import {CategoryPermissions} from './CategoryPermission';

export type Category = {
  id: number;
  code: string;
  labels: LabelCollection;
  root: Category | null;
};

export type EnrichCategory = {
  id: number;
  isRoot: boolean;
  template_uuid: string | null;
  root: EnrichCategory | null;
  properties: CategoryProperties;
  attributes: CategoryAttributes;
  permissions: CategoryPermissions;
};

export type CategoryProperties = {
  code: string;
  labels: LabelCollection;
};

export interface CategoryAttributes {
  [key: string]: CategoryAttributeValueWrapper;
}

export interface CategoryAttributeValueWrapper {
  data: CategoryAttributeValueData;
  channel: ChannelCode | null;
  locale: LocaleCode | null;
  attribute_code: CompositeKeyWithoutLocale;
}

type CategoryTextAttributeValueData = string;

export interface CategoryImageAttributeValueDataFileInfo {
  size?: number;
  file_path: string;
  mime_type?: string;
  extension?: string;
  original_filename: string;
}

export type CategoryImageAttributeValueData = CategoryImageAttributeValueDataFileInfo | null;

export type CategoryAttributeValueData = CategoryTextAttributeValueData | CategoryImageAttributeValueData;

export const isCategoryImageAttributeValueData = (
  data: CategoryAttributeValueData
): data is CategoryImageAttributeValueData =>
  data === null || (data.hasOwnProperty('original_filename') && data.hasOwnProperty('file_path'));

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
  templateLabel?: string;
  templateUuid?: string;
  templateCode?: string;
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

// This is the empty value for DSM Rich Text Editor
// If we do not use it in our model
// then we have trouble telling whether a category has been modified or not
export const RICH_TEXT_DEFAULT_VALUE = '<p></p>\n';

export const attributeDefaultValues: {[key in CategoryAttributeType]: CategoryAttributeValueData} = {
  text: '',
  textarea: '',
  richtext: RICH_TEXT_DEFAULT_VALUE,
  image: null,
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
    code: categoryTree.code,
    childrenIds: Array.isArray(categoryTree.children) ? categoryTree.children.map(child => child.id) : [],
    data: categoryTree,
    parentId: parent,
    type: categoryTree.isRoot ? 'root' : categoryTree.isLeaf ? 'leaf' : 'node',
    childrenStatus: categoryTree.children && categoryTree.children.length > 0 ? 'loaded' : 'idle',
  };
};

export {convertToCategoryTree, buildTreeNodeFromCategoryTree};
