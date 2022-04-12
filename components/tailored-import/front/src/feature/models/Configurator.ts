import {ValidationError} from '@akeneo-pim-community/shared';
import {Attribute} from './Attribute';
import {AttributeTarget} from './Target';
import {AttributeDataMapping} from './DataMapping';
import {Column, ColumnIdentifier} from './Configuration';
import {Operation} from './Operation';

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
