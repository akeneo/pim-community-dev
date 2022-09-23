import {Channel, getLocalesFromChannel} from '@akeneo-pim-community/shared';
import {Attribute} from './pim/Attribute';
import {getDefaultAssociationTypeSource, getDefaultAttributeSource, getDefaultPropertySource, Source} from './Source';
import {AssociationType} from './pim/AssociationType';
import {Requirement, RequirementType} from './Requirement';
import {getDefaultStaticSource} from './Source';

const MAX_COLUMN_COUNT = 1000;

type Target = {
  type: RequirementType;
  name: string;
  required: boolean;
};

type ConcatElement = {
  uuid: string;
  type: 'text' | 'source';
  value: string;
};

type ConcatFormat = {
  type: 'concat';
  elements: ConcatElement[];
  space_between: boolean;
};
type NoneFormat = {
  type: 'none';
};
type Format = ConcatFormat | NoneFormat;

type DataMapping = {
  uuid: string;
  target: Target;
  sources: Source[];
  format: Format;
};

const createDataMapping = (requirement: Requirement, uuid: string): DataMapping => {
  if (null === /^[0-9A-F]{8}-[0-9A-F]{4}-[4][0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i.exec(uuid)) {
    throw new Error(`DataMapping configuration creation requires a valid uuid: "${uuid}"`);
  }

  return {
    uuid,
    target: {
      name: requirement.code,
      type: requirement.type,
      required: requirement.required,
    },
    sources: [],
    format: getFormatForType(requirement.type),
  };
};

const getFormatForType = (type: RequirementType): Format => {
  switch (type) {
    case 'string':
    case 'limited_string':
      return {
        type: 'concat',
        elements: [],
        space_between: true,
      };
    case 'boolean':
    case 'number':
    case 'price':
    case 'date':
    case 'integer':
    case 'url':
    case 'measurement':
    case 'string_collection':
      return {
        type: 'none',
      };

    default:
      throw new Error(`Unsupported requirement type: ${type}`);
  }
};

const addDataMapping = (dataMappings: DataMapping[], dataMappingToAdd: DataMapping): DataMapping[] => [
  ...dataMappings,
  dataMappingToAdd,
];

const filterEmptyDataMappings = (dataMappings: DataMapping[]): DataMapping[] =>
  dataMappings.filter(dataMapping => 0 !== dataMapping.sources.length);

const removeDataMapping = (dataMappings: DataMapping[], dataMappingUuid: string): DataMapping[] =>
  dataMappings.filter(({uuid}) => uuid !== dataMappingUuid);

const updateDataMapping = (dataMappings: DataMapping[], updatedDataMapping: DataMapping): DataMapping[] =>
  filterEmptyDataMappings(
    dataMappings.map(dataMapping => (dataMapping.uuid === updatedDataMapping.uuid ? updatedDataMapping : dataMapping))
  );

const filterDataMappings = (dataMappings: DataMapping[], searchValue: string): DataMapping[] =>
  dataMappings.filter(({target}) => target.name && target.name.toLowerCase().includes(searchValue.toLowerCase()));

const addSource = (dataMapping: DataMapping, source: Source): DataMapping => {
  switch (dataMapping.target.type) {
    case 'string':
    case 'limited_string':
      return {
        ...dataMapping,
        sources: [...dataMapping.sources, source],
        format: {
          ...dataMapping.format,
          elements: [
            ...(dataMapping.format as ConcatFormat).elements,
            {uuid: source.uuid, type: 'source', value: source.uuid},
          ],
        } as Format,
      };
    case 'boolean':
    case 'measurement':
    case 'number':
    case 'price':
    case 'integer':
    case 'string_collection':
    case 'url':
      return {
        ...dataMapping,
        sources: [...dataMapping.sources, source],
      };

    default:
      throw new Error(`Unsupported requirement type for add source: ${dataMapping.target.type}`);
  }
};

const addAttributeSource = (dataMapping: DataMapping, attribute: Attribute, channels: Channel[]): DataMapping => {
  const channelCode = attribute.scopable ? channels[0].code : null;
  const locales = getLocalesFromChannel(channels, channelCode);
  const filteredLocaleSpecificLocales = attribute.is_locale_specific
    ? locales.filter(({code}) => attribute.available_locales.includes(code))
    : locales;
  const localeCode = attribute.localizable ? filteredLocaleSpecificLocales[0].code : null;

  return addSource(dataMapping, getDefaultAttributeSource(attribute, dataMapping.target, channelCode, localeCode));
};

const addAssociationTypeSource = (dataMapping: DataMapping, associationType: AssociationType): DataMapping =>
  addSource(dataMapping, getDefaultAssociationTypeSource(associationType));

const addPropertySource = (dataMapping: DataMapping, sourceCode: string, channels: Channel[]): DataMapping =>
  addSource(dataMapping, getDefaultPropertySource(sourceCode, dataMapping.target, channels));

const addStaticSource = (dataMapping: DataMapping, sourceCode: string, channels: Channel[]): DataMapping =>
  addSource(dataMapping, getDefaultStaticSource(sourceCode, channels));

const filterEmptyOperations = (operations: object) =>
  Object.keys(operations).reduce((accumulator, key) => {
    if (undefined !== operations[key]) {
      accumulator[key] = operations[key];
    }

    return accumulator;
  }, {});

const updateSource = (dataMapping: DataMapping, updatedSource: Source): DataMapping => {
  const filteredOperations = filterEmptyOperations(updatedSource.operations);

  return {
    ...dataMapping,
    sources: dataMapping.sources.map<Source>(source =>
      source.uuid === updatedSource.uuid ? {...updatedSource, operations: filteredOperations} : source
    ),
  };
};

const removeSource = (dataMapping: DataMapping, removedSource: Source): DataMapping => {
  switch (dataMapping.target.type) {
    case 'string':
    case 'limited_string':
      return {
        ...dataMapping,
        sources: dataMapping.sources.filter(source => source.uuid !== removedSource.uuid),
        format: {
          ...dataMapping.format,
          elements: (dataMapping.format as ConcatFormat).elements.filter(
            element => 'source' !== element.type || element.value !== removedSource.uuid
          ),
        } as Format,
      };
    case 'boolean':
    case 'number':
    case 'integer':
    case 'price':
    case 'measurement':
    case 'string_collection':
    case 'url':
      return {
        ...dataMapping,
        sources: dataMapping.sources.filter(source => source.uuid !== removedSource.uuid),
      };

    default:
      throw new Error(`Unsupported requirement type: ${dataMapping.target.type}`);
  }
};

export type {DataMapping, ConcatElement, Format, ConcatFormat, Target};
export {
  addAssociationTypeSource,
  addAttributeSource,
  addStaticSource,
  addDataMapping,
  addPropertySource,
  createDataMapping,
  filterDataMappings,
  filterEmptyOperations,
  MAX_COLUMN_COUNT,
  removeDataMapping,
  removeSource,
  updateDataMapping,
  updateSource,
  filterEmptyDataMappings,
};
