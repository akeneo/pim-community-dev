import {constant, clone} from 'lodash/fp';

import {LabelCollection, LocaleCode} from '@akeneo-pim-community/shared';
import {TreeNode} from './Tree';
import {buildCompositeKey, CompositeKey, CompositeKeyWithoutLocale} from './CompositeKey';
import {Template} from './Template';
import {Attribute, CategoryAttributeType} from './Attribute';

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

export interface CategoryAttributes {
  [key: CompositeKey]: CategoryAttributeValueWrapper;
}

export interface CategoryAttributeValueWrapper {
  data: CategoryAttributeValueData;
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

const attributeDefaultValues: {[key in CategoryAttributeType]: CategoryAttributeValueData} = {
  text: '',
  textarea: '',
  richtext: '', //'<p></p>\n',
  image: null,
};

export function getAttributeValue(
  attributes: CategoryAttributes,
  attribute: Attribute,
  localeCode: LocaleCode
): CategoryAttributeValueData | undefined {
  const compositeKey = buildCompositeKey(attribute, localeCode);
  const value = attributes[compositeKey];
  return value ? value.data : undefined;
}

/**
 * return a copy of the given category where all attributes defined in template are valued
 * @param category the category to normalize
 * @param template the template describing the attribute of this category
 * @param locales the locales to consider when generating default values
 * @return the normalized category
 */
function normalizeCategoryAttributes(
  category: EnrichCategory,
  template: Template,
  locales: LocaleCode[]
): EnrichCategory {
  const fixedCategory = clone(category);

  template.attributes.forEach((attribute: Attribute) => {
    const {code} = attribute;
    if (fixedCategory.attributes.hasOwnProperty(code)) return;

    fixedCategory.attributes = {
      ...buildCategoryAttributeValues(attribute, locales),
      // attributes values coming from the category in arguments must take precedence over generared ones
      ...fixedCategory.attributes,
    };
  });

  return fixedCategory;
}

/**
 * Generate a CategoryAttributes structure for a given attribute in the requested locales.
 * If the attribute in not localizable, then only one attribute value will be generated.
 * @param attribute the attribute for which we want to geenrate values
 * @param locales the locales to consider to build attribute values
 * @returns the attributes values
 */
function buildCategoryAttributeValues(attribute: Attribute, locales: LocaleCode[]): CategoryAttributes {
  const attributesValues = {};
  const applicableLocales = attribute.is_localizable ? locales : [null];
  for (const locale of applicableLocales) {
    const key = buildCompositeKey(attribute, locale);
    const keyNoLocale = locale === null ? key : buildCompositeKey(attribute, null);
    attributesValues[key] = {
      data: attributeDefaultValues[attribute.type],
      locale,
      attribute_code: keyNoLocale,
    };
  }
  return attributesValues;
}

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
