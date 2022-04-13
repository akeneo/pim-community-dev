import {uuid} from 'akeneo-design-system';
import {Channel, getLocalesFromChannel} from '@akeneo-pim-community/shared';
import {Column, ColumnIdentifier} from './Configuration';
import {Attribute} from './Attribute';
import {AttributeTarget, createPropertyTarget, createAttributeTarget, PropertyTarget} from './Target';
import {Operation} from './Operation';
import {SampleData} from './SampleData';

type DataMapping = AttributeDataMapping | PropertyDataMapping;

type AttributeDataMapping = {
  uuid: string;
  target: AttributeTarget;
  sources: ColumnIdentifier[];
  operations: Operation[];
  sample_data: SampleData[];
};

type PropertyDataMapping = {
  uuid: string;
  target: PropertyTarget;
  sources: ColumnIdentifier[];
  operations: Operation[];
  sample_data: SampleData[];
};

const MAX_DATA_MAPPING_COUNT = 500;
const MAX_SOURCE_COUNT_FOR_COLLECTION_TARGETS = 4;

type DataMappingType = 'attribute' | 'property';

const createDefaultDataMapping = (attribute: Attribute, identifierColumn: Column | null, sampleData: SampleData[]) => {
  const defaultDataMapping: DataMapping = {
    uuid: uuid(),
    target: createAttributeTarget(attribute, null, null),
    sources: [],
    operations: [],
    sample_data: sampleData,
  };

  return identifierColumn ? addSourceToDataMapping(defaultDataMapping, identifierColumn) : defaultDataMapping;
};

const createPropertyDataMapping = (code: string): DataMapping => {
  return {
    uuid: uuid(),
    target: createPropertyTarget(code),
    sources: [],
    operations: [],
    sample_data: [],
  };
};

const createAttributeDataMapping = (attribute: Attribute, channels: Channel[]): AttributeDataMapping => {
  const channel = attribute.scopable ? channels[0].code : null;
  const locales = getLocalesFromChannel(channels, channel);
  const filteredLocaleSpecificLocales = attribute.is_locale_specific
    ? locales.filter(({code}) => attribute.available_locales.includes(code))
    : locales;
  const locale = attribute.localizable ? filteredLocaleSpecificLocales[0].code : null;

  return {
    uuid: uuid(),
    target: createAttributeTarget(attribute, channel, locale),
    sources: [],
    operations: [],
    sample_data: [],
  };
};

const updateDataMapping = (dataMappings: DataMapping[], updatedDataMapping: DataMapping): DataMapping[] =>
  dataMappings.map(dataMapping => (dataMapping.uuid === updatedDataMapping.uuid ? updatedDataMapping : dataMapping));

const addSourceToDataMapping = (dataMapping: DataMapping, column: Column): DataMapping => ({
  ...dataMapping,
  sources: [...dataMapping.sources, column.uuid],
});

export type {AttributeDataMapping, DataMapping, DataMappingType, PropertyDataMapping};
export {
  MAX_DATA_MAPPING_COUNT,
  MAX_SOURCE_COUNT_FOR_COLLECTION_TARGETS,
  createAttributeDataMapping,
  createPropertyDataMapping,
  updateDataMapping,
  createDefaultDataMapping,
  addSourceToDataMapping,
};
