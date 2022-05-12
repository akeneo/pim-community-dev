import {
  CleanHTMLTagsOperation,
  CLEAN_HTML_TAGS_OPERATION_TYPE,
  getDefaultCleanHTMLTagsOperation,
  SIMPLE_SELECT_REPLACEMENT_OPERATION_TYPE,
  SimpleSelectReplacementOperation,
  getDefaultSimpleSelectReplacementOperation,
  SplitOperation,
  SPLIT_OPERATION_TYPE,
  getDefaultSplitOperation,
} from '../components/DataMappingDetails/Operation';

type Operation = CleanHTMLTagsOperation | SplitOperation | SimpleSelectReplacementOperation;

type OperationType = Operation['type'];

const getDefaultOperation = (operationType: OperationType): Operation => {
  switch (operationType) {
    case CLEAN_HTML_TAGS_OPERATION_TYPE:
      return getDefaultCleanHTMLTagsOperation();
    case SPLIT_OPERATION_TYPE:
      return getDefaultSplitOperation();
    case SIMPLE_SELECT_REPLACEMENT_OPERATION_TYPE:
      return getDefaultSimpleSelectReplacementOperation();
    default:
      throw new Error(`Invalid operation type: "${operationType}"`);
  }
};

export {getDefaultOperation};
export type {Operation, OperationType};
