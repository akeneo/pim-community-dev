import {Channel, ChannelReference, getLocalesFromChannel, LocaleReference} from '@akeneo-pim-community/shared';
import {uuid} from 'akeneo-design-system';
import {Attribute} from './Attribute';
import {Selection, getDefaultSelectionByAttribute, getDefaultSelectionByProperty} from './Selection';

type Operation = {
  type: string;
  value?: any;
  mapping?: any;
  unit?: any;
};

type Source = {
  uuid: string;
  code: string;
  type: 'attribute' | 'property';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: Operation[];
  selection: Selection;
};

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
  sourceCode: string,
  attribute: Attribute,
  channels: Channel[]
): ColumnConfiguration => {
  const channelCode = attribute.scopable ? channels[0].code : null;
  const locales = getLocalesFromChannel(channels, channelCode);
  const filteredLocaleSpecificLocales = attribute.is_locale_specific
    ? locales.filter(({code}) => attribute.available_locales.includes(code))
    : locales;
  const locale = attribute.localizable ? filteredLocaleSpecificLocales[0].code : null;
  const selection = getDefaultSelectionByAttribute(attribute);

  return {
    ...columnConfiguration,
    sources: [
      ...columnConfiguration.sources,
      {
        uuid: uuid(),
        code: sourceCode,
        type: 'attribute',
        locale,
        channel: channelCode,
        operations: [],
        selection,
      },
    ],
  };
};

const addPropertySource = (columnConfiguration: ColumnConfiguration, sourceCode: string): ColumnConfiguration => {
  const selection = getDefaultSelectionByProperty(sourceCode);

  return {
    ...columnConfiguration,
    sources: [
      ...columnConfiguration.sources,
      {
        uuid: uuid(),
        code: sourceCode,
        type: 'property',
        locale: null,
        channel: null,
        operations: [],
        selection,
      },
    ],
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

export type {ColumnConfiguration, Source};
export {
  createColumn,
  addColumn,
  removeColumn,
  updateColumn,
  removeSource,
  addAttributeSource,
  addPropertySource,
  updateSource,
};
