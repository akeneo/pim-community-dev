import {ValidationError} from '@akeneo-pim-community/shared';
import {Operation, OperationPreviewData, OperationType} from '../../../../models';

type OperationBlockProps = {
  targetReferenceDataName?: string;
  targetCode: string;
  operation: Operation;
  previewData: {
    data: OperationPreviewData;
    isLoading: boolean;
    hasError: boolean;
  };
  isLastOperation: boolean;
  onChange: (operation: Operation) => void;
  onRemove: (operationType: OperationType) => void;
  validationErrors: ValidationError[];
};

export type {OperationBlockProps};
