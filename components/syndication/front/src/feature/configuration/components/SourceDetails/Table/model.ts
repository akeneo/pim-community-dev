import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Source} from '../../../models';
import {DefaultValueOperation, isDefaultValueOperation} from '../common';

type TableOperations = {
  default_value?: DefaultValueOperation;
};

type TableSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: TableOperations;
  selection: {type: 'raw'};
};

const getDefaultTableSource = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): TableSource => ({
  uuid: uuid(),
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  operations: {},
  selection: {type: 'raw'},
});

const isTableOperations = (operations: Object): operations is TableOperations => {
  return Object.entries(operations).every(([type, operation]) => {
    switch (type) {
      case 'default_value':
        return isDefaultValueOperation(operation);
      default:
        return false;
    }
  });
};

const isTableSource = (source: Source): source is TableSource =>
  'type' in source.selection && 'raw' === source.selection.type && isTableOperations(source.operations);

export type {TableSource};
export {getDefaultTableSource, isTableSource};
