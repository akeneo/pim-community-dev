import {Operation, OperationType} from '../../../models';

type OperationBlockProps = {
  operation: Operation;
  onRemove: (operationType: OperationType) => void;
};

export type {OperationBlockProps};
