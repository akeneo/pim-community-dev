import {ColumnIdentifier} from './Configuration';
import {uuid} from 'akeneo-design-system';
import {LocaleReference, ChannelReference} from '@akeneo-pim-community/shared';
import {Column} from '.';

type TargetAction = 'set' | 'add';
type TargetEmptyAction = 'clear' | 'skip';
type TargetErrorAction = 'skipLine' | 'skipValue';

type AttributeTarget = {
  code: string;
  channel: ChannelReference;
  locale: LocaleReference;
  type: 'attribute';
  action: TargetAction;
  ifEmpty: TargetEmptyAction;
  onError: TargetErrorAction;
};

type PropertyTarget = {
  code: string;
  type: 'property';
  action: 'set' | 'add';
  ifEmpty: 'clear' | 'skip';
  onError: 'skipLine' | 'skipValue';
};

type DataMapping = {
  uuid: string;
  target: AttributeTarget | PropertyTarget;
  sources: ColumnIdentifier[];
  operations: [];
  sampleData: [];
};

const MAX_DATA_MAPPING_COUNT = 500;
const MAX_SOURCE_COUNT_BY_DATA_MAPPING = 4;

type DataMappingType = 'attribute' | 'property';

const createDefaultDataMapping = (columns: Column[]) => {
  const defaultDataMapping = createDataMapping('sku', 'attribute');

  return columns.length > 0 ? addSourceToDataMapping(defaultDataMapping, columns[0]) : defaultDataMapping;
};

const createDataMapping = (code: string, type: DataMappingType): DataMapping => {
  return {
    uuid: uuid(),
    target: 'attribute' === type ? createAttributeTarget(code, null, null) : createPropertyTarget(code),
    sources: [],
    operations: [],
    sampleData: [],
  };
};

const updateDataMapping = (dataMappings: DataMapping[], updatedDataMapping: DataMapping): DataMapping[] =>
  dataMappings.map(dataMapping => (dataMapping.uuid === updatedDataMapping.uuid ? updatedDataMapping : dataMapping));

const addSourceToDataMapping = (dataMapping: DataMapping, column: Column): DataMapping => {
  return {...dataMapping, sources: [...dataMapping.sources, column.uuid]};
};

const createAttributeTarget = (code: string, channel: ChannelReference, locale: LocaleReference): AttributeTarget => {
  return {
    code,
    type: 'attribute',
    locale,
    channel,
    action: 'set',
    ifEmpty: 'skip',
    onError: 'skipLine',
  };
};

const createPropertyTarget = (code: string): PropertyTarget => {
  return {
    code,
    type: 'property',
    action: 'set',
    ifEmpty: 'skip',
    onError: 'skipLine',
  };
};

export type {DataMapping, DataMappingType};
export {
  MAX_DATA_MAPPING_COUNT,
  MAX_SOURCE_COUNT_BY_DATA_MAPPING,
  createDataMapping,
  updateDataMapping,
  createDefaultDataMapping,
  addSourceToDataMapping,
};
