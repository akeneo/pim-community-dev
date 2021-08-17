import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Source} from '../../../models';
import {DefaultValueOperation, isDefaultValueOperation} from '../common';

type TextOperations = {
  default_value?: DefaultValueOperation;
};

type TextSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: TextOperations;
  selection: {type: 'code'};
};

const getDefaultTextSource = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): TextSource => ({
  uuid: uuid(),
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  operations: {},
  selection: {type: 'code'},
});

const isTextOperations = (operations: Object): operations is TextOperations => {
  return Object.entries(operations).every(([type, operation]) => {
    switch (type) {
      case 'default_value':
        return isDefaultValueOperation(operation);
      default:
        return false;
    }
  });
};

const isTextSource = (source: Source): source is TextSource =>
  'type' in source.selection && 'code' === source.selection.type && isTextOperations(source.operations);

export type {TextSource};
export {getDefaultTextSource, isTextSource};
