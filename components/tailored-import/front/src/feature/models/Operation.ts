import {
  CleanHTMLTagsOperation,
  CLEAN_HTML_TAGS_TYPE,
  getDefaultCleanHTMLTagsOperation,
} from '../components/DataMappingDetails/Operation';

type Operation = CleanHTMLTagsOperation;

type OperationType = Operation['type'];

const getDefaultOperation = (operationType: OperationType): Operation => {
  switch (operationType) {
    case CLEAN_HTML_TAGS_TYPE:
      return getDefaultCleanHTMLTagsOperation();
    default:
      throw new Error(`Invalid operation type: "${operationType}"`);
  }
};

export {getDefaultOperation};
export type {Operation, OperationType};
