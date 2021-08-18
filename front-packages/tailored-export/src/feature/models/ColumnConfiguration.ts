import {Channel, getLocalesFromChannel} from '@akeneo-pim-community/shared';
import {Attribute} from './Attribute';
import {getDefaultAssociationTypeSource, getDefaultAttributeSource, getDefaultPropertySource, Source} from './Source';
import {AssociationType} from './AssociationType';

const MAX_COLUMN_COUNT = 1000;

type ConcatElement = {
  uuid: string;
  type: 'text' | 'source';
  value: string;
};

type Format = {
  type: 'concat';
  elements: ConcatElement[];
  space_between?: boolean;
};

type ColumnConfiguration = {
  uuid: string;
  target: string;
  sources: Source[];
  format: Format;
};

type ColumnsState = {
  columns: ColumnConfiguration[];
  selectedColumnUuid: string | null;
};

const createColumn = (newColumnName: string, uuid: string): ColumnConfiguration => {
  if (null === /^[0-9A-F]{8}-[0-9A-F]{4}-[4][0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i.exec(uuid)) {
    throw new Error(`Column configuration creation requires a valid uuid: "${uuid}"`);
  }

  return {
    uuid,
    target: newColumnName,
    sources: [],
    format: {
      type: 'concat',
      elements: [],
    },
  };
};

const addColumn = (columns: ColumnConfiguration[], columnToAdd: ColumnConfiguration): ColumnConfiguration[] => [
  ...columns,
  columnToAdd,
];

const removeColumn = (columns: ColumnConfiguration[], columnUuid: string): ColumnConfiguration[] =>
  columns.filter(({uuid}) => uuid !== columnUuid);

const updateColumn = (columns: ColumnConfiguration[], updatedColumn: ColumnConfiguration): ColumnConfiguration[] =>
  columns.map(column => (column.uuid === updatedColumn.uuid ? updatedColumn : column)).filter(isNonEmptyColumn);

const isNonEmptyColumn = (columnConfiguration: ColumnConfiguration): boolean =>
  '' !== columnConfiguration.target ||
  0 !== columnConfiguration.sources.length ||
  0 !== columnConfiguration.format.elements.length;

const filterColumns = (columns: ColumnConfiguration[], searchValue: string): ColumnConfiguration[] =>
  columns.filter(({target}) => target.toLowerCase().includes(searchValue.toLowerCase()));

const addSource = (columnConfiguration: ColumnConfiguration, source: Source): ColumnConfiguration => ({
  ...columnConfiguration,
  sources: [...columnConfiguration.sources, source],
  format: {
    ...columnConfiguration.format,
    elements: [...columnConfiguration.format.elements, {uuid: source.uuid, type: 'source', value: source.uuid}],
  },
});

const addAttributeSource = (
  columnConfiguration: ColumnConfiguration,
  attribute: Attribute,
  channels: Channel[]
): ColumnConfiguration => {
  const channelCode = attribute.scopable ? channels[0].code : null;
  const locales = getLocalesFromChannel(channels, channelCode);
  const filteredLocaleSpecificLocales = attribute.is_locale_specific
    ? locales.filter(({code}) => attribute.available_locales.includes(code))
    : locales;
  const locale = attribute.localizable ? filteredLocaleSpecificLocales[0].code : null;

  return addSource(columnConfiguration, getDefaultAttributeSource(attribute, channelCode, locale));
};

const addAssociationTypeSource = (
  columnConfiguration: ColumnConfiguration,
  associationType: AssociationType
): ColumnConfiguration => addSource(columnConfiguration, getDefaultAssociationTypeSource(associationType));

const addPropertySource = (columnConfiguration: ColumnConfiguration, sourceCode: string): ColumnConfiguration =>
  addSource(columnConfiguration, getDefaultPropertySource(sourceCode));

const filterEmptyOperations = (operations: object) =>
  Object.keys(operations).reduce((accumulator, key) => {
    if (undefined !== operations[key]) {
      accumulator[key] = operations[key];
    }

    return accumulator;
  }, {});

const updateSource = (columnConfiguration: ColumnConfiguration, updatedSource: Source): ColumnConfiguration => {
  const filteredOperations = filterEmptyOperations(updatedSource.operations);

  return {
    ...columnConfiguration,
    sources: columnConfiguration.sources.map<Source>(source =>
      source.uuid === updatedSource.uuid ? {...updatedSource, operations: filteredOperations} : source
    ),
  };
};

const removeSource = (columnConfiguration: ColumnConfiguration, removedSource: Source): ColumnConfiguration => ({
  ...columnConfiguration,
  sources: columnConfiguration.sources.filter(source => source.uuid !== removedSource.uuid),
  format: {
    ...columnConfiguration.format,
    elements: columnConfiguration.format.elements.filter(
      element => 'source' !== element.type || element.value !== removedSource.uuid
    ),
  },
});

export type {ColumnConfiguration, ColumnsState, ConcatElement, Format};
export {
  addAssociationTypeSource,
  addAttributeSource,
  addColumn,
  addPropertySource,
  createColumn,
  filterColumns,
  filterEmptyOperations,
  MAX_COLUMN_COUNT,
  removeColumn,
  removeSource,
  updateColumn,
  updateSource,
};
