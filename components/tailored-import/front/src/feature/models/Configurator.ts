import {ValidationError} from '@akeneo-pim-community/shared';
import {AttributeDataMapping, Attribute, Column, Operation, ColumnIdentifier, AttributeTarget} from '../models';

type AttributeDataMappingConfiguratorProps = {
  dataMapping: AttributeDataMapping;
  attribute: Attribute;
  columns: Column[];
  validationErrors: ValidationError[];
  onOperationsChange: (operations: Operation[]) => void;
  onRefreshSampleData: (index: number) => Promise<void>;
  onSourcesChange: (sources: ColumnIdentifier[]) => void;
  onTargetChange: (target: AttributeTarget) => void;
};

export type {AttributeDataMappingConfiguratorProps};
