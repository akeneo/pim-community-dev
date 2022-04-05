import {ValidationError} from '@akeneo-pim-community/shared';
import {Attribute} from './Attribute';
import {AttributeTarget} from './Target';
import {AttributeDataMapping} from './DataMapping';
import {Column, ColumnIdentifier} from './Configuration';

type AttributeDataMappingConfiguratorProps = {
  dataMapping: AttributeDataMapping;
  attribute: Attribute;
  columns: Column[];
  validationErrors: ValidationError[];
  onTargetChange: (target: AttributeTarget) => void;
  onRefreshSampleData: (index: number) => Promise<void>;
  onSourcesChange: (sources: ColumnIdentifier[]) => void;
};

export type {AttributeDataMappingConfiguratorProps};
