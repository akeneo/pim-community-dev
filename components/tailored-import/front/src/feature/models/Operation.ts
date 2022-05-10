import {
  CleanHTMLTagsOperation,
  CLEAN_HTML_TAGS_OPERATION_TYPE,
  getDefaultCleanHTMLTagsOperation,
  SplitOperation,
  getDefaultSplitOperation,
  SPLIT_OPERATION_TYPE,
} from '../components/DataMappingDetails/Operation';

type Operation = CleanHTMLTagsOperation | SplitOperation;

type OperationType = Operation['type'];

const getDefaultOperation = (operationType: OperationType): Operation => {
  switch (operationType) {
    case CLEAN_HTML_TAGS_OPERATION_TYPE:
      return getDefaultCleanHTMLTagsOperation();
    case SPLIT_OPERATION_TYPE:
      return getDefaultSplitOperation();
    default:
      throw new Error(`Invalid operation type: "${operationType}"`);
  }
};

export {getDefaultOperation};
export type {Operation, OperationType};
