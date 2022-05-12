import {Operation, OperationType} from '../../../models';

type OperationBlockProps = {
  targetCode: string;
  operation: Operation;
  onChange: (operation: Operation) => void;
  onRemove: (operationType: OperationType) => void;
};

export type {OperationBlockProps};
