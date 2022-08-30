import {
  BooleanReplacementOperation,
  CATEGORIES_REPLACEMENT_OPERATION_TYPE,
  CategoriesReplacementOperation,
  CHANGE_CASE_OPERATION_TYPE,
  ChangeCaseOperation,
  CLEAN_HTML_TAGS_OPERATION_TYPE,
  CleanHTMLTagsOperation,
  EnabledReplacementOperation,
  FAMILY_REPLACEMENT_OPERATION_TYPE,
  FamilyReplacementOperation,
  getDefaultBooleanReplacementOperation,
  getDefaultCategoriesReplacementOperation,
  getDefaultCleanHTMLTagsOperation,
  getDefaultEnabledReplacementOperation,
  getDefaultFamilyReplacementOperation,
  getDefaultMultiSelectReplacementOperation,
  getDefaultSimpleSelectReplacementOperation,
  getDefaultSplitOperation,
  getDefaultChangeCaseOperation,
  MULTI_SELECT_REPLACEMENT_OPERATION_TYPE,
  MultiSelectReplacementOperation,
  SIMPLE_SELECT_REPLACEMENT_OPERATION_TYPE,
  SimpleSelectReplacementOperation,
  SPLIT_OPERATION_TYPE,
  SplitOperation,
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
  | SplitOperation
  | ChangeCaseOperation;
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
    case CHANGE_CASE_OPERATION_TYPE:
      return getDefaultChangeCaseOperation();
    default:
      throw new Error(`Invalid operation type: "${operationType}"`);
  }
};

export {getDefaultOperation, getAttributeRequiredOperations, getPropertyRequiredOperations};
export type {Operation, OperationType};
