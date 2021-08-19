import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Attribute, Source} from '../../../models';
import {
  DefaultValueOperation,
  isDefaultValueOperation,
  CodeLabelCollectionSelection,
  isCodeLabelCollectionSelection,
} from '../common';

type MultiSelectOperations = {
  default_value?: DefaultValueOperation;
};

type MultiSelectSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: MultiSelectOperations;
  selection: CodeLabelCollectionSelection;
};

const getDefaultMultiSelectSource = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): MultiSelectSource => ({
  uuid: uuid(),
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  operations: {},
  selection: {type: 'code', separator: ','},
});

const isMultiSelectOperations = (operations: Object): operations is MultiSelectOperations =>
  Object.entries(operations).every(([type, operation]) => {
    switch (type) {
      case 'default_value':
        return isDefaultValueOperation(operation);
      default:
        return false;
    }
  });

const isMultiSelectSource = (source: Source): source is MultiSelectSource =>
  isCodeLabelCollectionSelection(source.selection) && isMultiSelectOperations(source.operations);

export {getDefaultMultiSelectSource, isMultiSelectSource};
export type {MultiSelectSource};
