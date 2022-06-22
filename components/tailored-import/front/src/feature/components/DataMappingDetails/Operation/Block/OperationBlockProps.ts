import {ValidationError} from '@akeneo-pim-community/shared';
import {Operation, OperationType, PreviewData} from '../../../../models';

type OperationBlockProps = {
  targetCode: string;
  operation: Operation;
  previewData: {
    data: PreviewData[];
    isLoading: boolean;
    hasError: boolean;
  };
  isLastOperation: boolean;
  onChange: (operation: Operation) => void;
  onRemove: (operationType: OperationType) => void;
  validationErrors: ValidationError[];
};

export type {OperationBlockProps};
