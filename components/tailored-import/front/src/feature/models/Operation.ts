import {
  BooleanReplacementOperation,
  CATEGORIES_REPLACEMENT_OPERATION_TYPE,
  CategoriesReplacementOperation,
  CHANGE_CASE_OPERATION_TYPE,
  ChangeCaseOperation,
  CLEAN_HTML_OPERATION_TYPE,
  CleanHTMLOperation,
  EnabledReplacementOperation,
  FAMILY_REPLACEMENT_OPERATION_TYPE,
  FamilyReplacementOperation,
  getDefaultBooleanReplacementOperation,
  getDefaultCategoriesReplacementOperation,
  getDefaultChangeCaseOperation,
  getDefaultCleanHTMLOperation,
  getDefaultEnabledReplacementOperation,
  getDefaultFamilyReplacementOperation,
  getDefaultMultiSelectReplacementOperation,
  getDefaultRemoveWhitespaceOperation,
  getDefaultSimpleSelectReplacementOperation,
  getDefaultSplitOperation,
  MULTI_SELECT_REPLACEMENT_OPERATION_TYPE,
  MultiSelectReplacementOperation,
  REMOVE_WHITESPACE_OPERATION_TYPE,
  RemoveWhitespaceOperation,
  SIMPLE_SELECT_REPLACEMENT_OPERATION_TYPE,
  SimpleSelectReplacementOperation,
  SPLIT_OPERATION_TYPE,
  SplitOperation,
} from '../components/DataMappingDetails/Operation';
import {Attribute} from './Attribute';

type Operation =
  | BooleanReplacementOperation
  | CategoriesReplacementOperation
  | CleanHTMLOperation
  | EnabledReplacementOperation
  | MultiSelectReplacementOperation
  | SimpleSelectReplacementOperation
  | FamilyReplacementOperation
  | SplitOperation
  | ChangeCaseOperation
  | RemoveWhitespaceOperation;
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
    case CLEAN_HTML_OPERATION_TYPE:
      return getDefaultCleanHTMLOperation();
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
    case REMOVE_WHITESPACE_OPERATION_TYPE:
      return getDefaultRemoveWhitespaceOperation();
    default:
      throw new Error(`Invalid operation type: "${operationType}"`);
  }
};

export {getDefaultOperation, getAttributeRequiredOperations, getPropertyRequiredOperations};
export type {Operation, OperationType};
