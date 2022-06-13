import {
  CLEAN_HTML_TAGS_OPERATION_TYPE,
  CleanHTMLTagsOperation,
  getDefaultCleanHTMLTagsOperation,
  getDefaultMultiSelectReplacementOperation,
  getDefaultSimpleSelectReplacementOperation,
  getDefaultSplitOperation,
  MULTI_SELECT_REPLACEMENT_OPERATION_TYPE,
  MultiSelectReplacementOperation,
  SIMPLE_SELECT_REPLACEMENT_OPERATION_TYPE,
  SimpleSelectReplacementOperation,
  SPLIT_OPERATION_TYPE,
  SplitOperation,
} from '../components/DataMappingDetails/Operation';

type Operation =
  | CleanHTMLTagsOperation
  | MultiSelectReplacementOperation
  | SimpleSelectReplacementOperation
  | SplitOperation;

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
    default:
      throw new Error(`Invalid operation type: "${operationType}"`);
  }
};

export {getDefaultOperation};
export type {Operation, OperationType};
