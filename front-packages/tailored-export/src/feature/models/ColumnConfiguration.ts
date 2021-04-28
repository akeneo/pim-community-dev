import {ChannelReference, LocaleCode, LocaleReference} from '@akeneo-pim-community/shared';
import {uuid} from 'akeneo-design-system';


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

const createColumn = (newColumnName: string): ColumnConfiguration => {
    return {
        uuid: uuid(),
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

const renameColumnTarget = (
    columns: ColumnsConfiguration,
    uuid: string,
    updatedTarget: string
): ColumnsConfiguration => {
    return columns.map(column => {
        if (column.uuid !== uuid) return column;

        return {...column, target: updatedTarget};
    });
};

export type {ColumnConfiguration, ColumnsConfiguration};
export {createColumn, addColumn, removeColumn, renameColumnTarget};