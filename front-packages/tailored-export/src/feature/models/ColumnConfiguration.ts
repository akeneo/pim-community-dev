import {Channel, getLocalesFromChannel} from '@akeneo-pim-community/shared';
import {Attribute} from './Attribute';
import {getDefaultAssociationTypeSource, getDefaultAttributeSource, getDefaultPropertySource, Source} from './Source';
import {AssociationType} from './AssociationType';

const MAX_COLUMN_COUNT = 1000;

type ConcatElement = {
  type: 'string' | 'source';
  value: string;
};

type Format = {
  type: 'concat';
  elements: ConcatElement[];
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
  columns.filter(column => column.uuid !== columnUuid);

const updateColumn = (columns: ColumnConfiguration[], updatedColumn: ColumnConfiguration): ColumnConfiguration[] =>
  columns
    .map(column => {
      if (column.uuid !== updatedColumn.uuid) return column;

      return updatedColumn;
    })
    .filter(isNonEmptyColumn);

const isNonEmptyColumn = (columnConfiguration: ColumnConfiguration): boolean =>
  '' !== columnConfiguration.target ||
  0 !== columnConfiguration.sources.length ||
  0 !== columnConfiguration.format.elements.length;

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

  return {
    ...columnConfiguration,
    sources: [...columnConfiguration.sources, getDefaultAttributeSource(attribute, channelCode, locale)],
  };
};

const addAssociationTypeSource = (
  columnConfiguration: ColumnConfiguration,
  associationType: AssociationType
): ColumnConfiguration => {
  return {
    ...columnConfiguration,
    sources: [...columnConfiguration.sources, getDefaultAssociationTypeSource(associationType)],
  };
};

const addPropertySource = (columnConfiguration: ColumnConfiguration, sourceCode: string): ColumnConfiguration => {
  return {
    ...columnConfiguration,
    sources: [...columnConfiguration.sources, getDefaultPropertySource(sourceCode)],
  };
};

const updateSource = (columnConfiguration: ColumnConfiguration, updatedSource: Source): ColumnConfiguration => ({
  ...columnConfiguration,
  sources: columnConfiguration.sources.map<Source>(source =>
    source.uuid === updatedSource.uuid ? updatedSource : source
  ),
});

const removeSource = (columnConfiguration: ColumnConfiguration, removedSource: Source): ColumnConfiguration => ({
  ...columnConfiguration,
  sources: columnConfiguration.sources.filter(source => source.uuid !== removedSource.uuid),
});

export type {ColumnConfiguration, ColumnsState};
export {
  addColumn,
  createColumn,
  removeColumn,
  updateColumn,
  removeSource,
  addAttributeSource,
  addAssociationTypeSource,
  addPropertySource,
  updateSource,
  MAX_COLUMN_COUNT,
};
