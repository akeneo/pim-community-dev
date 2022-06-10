import {
  CLEAN_HTML_TAGS_OPERATION_TYPE,
  CleanHTMLTagsOperation,
  getDefaultCleanHTMLTagsOperation,
  getDefaultMultiSelectReplacementOperation,
  getDefaultSimpleSelectReplacementOperation,
  getDefaultSplitOperation,
  getDefaultCategoryReplacementOperation,
  MULTI_SELECT_REPLACEMENT_OPERATION_TYPE,
  MultiSelectReplacementOperation,
  SIMPLE_SELECT_REPLACEMENT_OPERATION_TYPE,
  SimpleSelectReplacementOperation,
  SPLIT_OPERATION_TYPE,
  SplitOperation,
  CATEGORY_REPLACEMENT_OPERATION_TYPE,
  CategoryReplacementOperation,
} from '../components/DataMappingDetails/Operation';

type Operation =
  | CleanHTMLTagsOperation
  | MultiSelectReplacementOperation
  | SimpleSelectReplacementOperation
  | SplitOperation
  | CategoryReplacementOperation;

type OperationType = Operation['type'];

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
    case CATEGORY_REPLACEMENT_OPERATION_TYPE:
      return getDefaultCategoryReplacementOperation();
    default:
      throw new Error(`Invalid operation type: "${operationType}"`);
  }
};

export {getDefaultOperation};
export type {Operation, OperationType};
