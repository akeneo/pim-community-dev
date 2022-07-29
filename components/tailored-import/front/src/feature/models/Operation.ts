import {
  CATEGORIES_REPLACEMENT_OPERATION_TYPE,
  CLEAN_HTML_TAGS_OPERATION_TYPE,
  FAMILY_REPLACEMENT_OPERATION_TYPE,
  MULTI_SELECT_REPLACEMENT_OPERATION_TYPE,
  SIMPLE_SELECT_REPLACEMENT_OPERATION_TYPE,
  SPLIT_OPERATION_TYPE,
  BooleanReplacementOperation,
  CategoriesReplacementOperation,
  CleanHTMLTagsOperation,
  EnabledReplacementOperation,
  FamilyReplacementOperation,
  MultiSelectReplacementOperation,
  SimpleSelectReplacementOperation,
  SplitOperation,
  getDefaultBooleanReplacementOperation,
  getDefaultCategoriesReplacementOperation,
  getDefaultCleanHTMLTagsOperation,
  getDefaultEnabledReplacementOperation,
  getDefaultFamilyReplacementOperation,
  getDefaultMultiSelectReplacementOperation,
  getDefaultSimpleSelectReplacementOperation,
  getDefaultSplitOperation,
} from '../components/DataMappingDetails/Operation';
import {Attribute} from './Attribute';

type Operation =
  | BooleanReplacementOperation
  | CategoriesReplacementOperation
  | CleanHTMLTagsOperation
  | EnabledReplacementOperation
  | MultiSelectReplacementOperation
  | SimpleSelectReplacementOperation
  | FamilyReplacementOperation
  | SplitOperation;

type OperationType = Operation['type'];

const getAttributeRequiredOperations = (attribute: Attribute): Operation[] => {
  switch (attribute.type) {
    case 'pim_catalog_boolean':
      return [getDefaultBooleanReplacementOperation()];
    default:
      return [];
  }
};

const getPropertyRequiredOperations = (propertyCode: string): Operation[] => {
  switch (propertyCode) {
    case 'enabled':
      return [getDefaultEnabledReplacementOperation()];
    default:
      return [];
  }
};

const getDefaultOperation = (operationType: OperationType): Operation => {
  switch (operationType) {
    case CLEAN_HTML_TAGS_OPERATION_TYPE:
      return getDefaultCleanHTMLTagsOperation();
    case SPLIT_OPERATION_TYPE:
      return getDefaultSplitOperation();
    case SIMPLE_SELECT_REPLACEMENT_OPERATION_TYPE:
      return getDefaultSimpleSelectReplacementOperation();
    case MULTI_SELECT_REPLACEMENT_OPERATION_TYPE:
      return getDefaultMultiSelectReplacementOperation();
    case CATEGORIES_REPLACEMENT_OPERATION_TYPE:
      return getDefaultCategoriesReplacementOperation();
    case FAMILY_REPLACEMENT_OPERATION_TYPE:
      return getDefaultFamilyReplacementOperation();
    default:
      throw new Error(`Invalid operation type: "${operationType}"`);
  }
};

export {getDefaultOperation, getAttributeRequiredOperations, getPropertyRequiredOperations};
export type {Operation, OperationType};
