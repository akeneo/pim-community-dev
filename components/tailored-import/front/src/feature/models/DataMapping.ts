import {uuid} from 'akeneo-design-system';
import {Channel, getLocalesFromChannel} from '@akeneo-pim-community/shared';
import {Column, ColumnIdentifier} from './Configuration';
import {Attribute} from './Attribute';
import {AttributeTarget, createAttributeTarget, createPropertyTarget, PropertyTarget} from './Target';

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
  const defaultDataMapping: DataMapping = {
    uuid: uuid(),
    target: createAttributeTarget('sku', null, null),
    sources: [],
    operations: [],
    sampleData: [],
  };

  return columns.length > 0 ? addSourceToDataMapping(defaultDataMapping, columns[0]) : defaultDataMapping;
};

const createPropertyDataMapping = (code: string): DataMapping => {
  return {
    uuid: uuid(),
    target: createPropertyTarget(code),
    sources: [],
    operations: [],
    sampleData: [],
  };
};

const createAttributeDataMapping = (code: string, attribute: Attribute, channels: Channel[]): DataMapping => {
  const channel = attribute.scopable ? channels[0].code : null;
  const locales = getLocalesFromChannel(channels, channel);
  const filteredLocaleSpecificLocales = attribute.is_locale_specific
    ? locales.filter(({code}) => attribute.available_locales.includes(code))
    : locales;
  const locale = attribute.localizable ? filteredLocaleSpecificLocales[0].code : null;

  return {
    uuid: uuid(),
    target: createAttributeTarget(code, channel, locale),
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

export type {DataMapping, DataMappingType};
export {
  MAX_DATA_MAPPING_COUNT,
  MAX_SOURCE_COUNT_BY_DATA_MAPPING,
  createAttributeDataMapping,
  createPropertyDataMapping,
  updateDataMapping,
  createDefaultDataMapping,
  addSourceToDataMapping,
};
