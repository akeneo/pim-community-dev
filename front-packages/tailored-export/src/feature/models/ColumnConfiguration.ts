import {ChannelReference, LocaleCode, LocaleReference} from '@akeneo-pim-community/shared';

type Operation = {
  type: string;
  value?: any;
  mapping?: any;
  unit?: any;
};

type Selection =
  | {
      type: 'code';
    }
  | {
      type: 'amount';
    }
  | {
      type: 'label';
      locale: LocaleCode;
    };

type Source = {
  uuid: string;
  code: string;
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

type ColumnsConfiguration = ColumnConfiguration[];

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

const addColumn = (columns: ColumnsConfiguration, columnToAdd: ColumnConfiguration): ColumnsConfiguration => [
  ...columns,
  columnToAdd,
];

const removeColumn = (columns: ColumnsConfiguration, columnUuid: string): ColumnsConfiguration =>
  columns.filter(column => column.uuid !== columnUuid);

const updateColumn = (columns: ColumnsConfiguration, updatedColumn: ColumnConfiguration): ColumnsConfiguration =>
  columns.map(column => {
    if (column.uuid !== updatedColumn.uuid) return column;

    return updatedColumn;
  });

export type {ColumnConfiguration, ColumnsConfiguration};
export {createColumn, addColumn, removeColumn, updateColumn};
