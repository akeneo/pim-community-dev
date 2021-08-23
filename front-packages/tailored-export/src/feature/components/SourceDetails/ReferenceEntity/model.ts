import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Source, Attribute} from '../../../models';
import {
  CodeLabelSelection,
  getDefaultCodeLabelSelection,
  isCodeLabelSelection,
  DefaultValueOperation,
  isDefaultValueOperation,
} from '../common';

type ReferenceEntityOperations = {
  default_value?: DefaultValueOperation;
};

type ReferenceEntitySource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: ReferenceEntityOperations;
  selection: CodeLabelSelection;
};

const getDefaultReferenceEntitySource = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): ReferenceEntitySource => ({
  uuid: uuid(),
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  operations: {},
  selection: getDefaultCodeLabelSelection(),
});

const isReferenceEntityOperations = (operations: Object): operations is ReferenceEntityOperations =>
  Object.entries(operations).every(([type, operation]) => {
    switch (type) {
      case 'default_value':
        return isDefaultValueOperation(operation);
      default:
        return false;
    }
  });

const isReferenceEntitySource = (source: Source): source is ReferenceEntitySource =>
  isCodeLabelSelection(source.selection) && isReferenceEntityOperations(source.operations);

export {isReferenceEntitySource, getDefaultReferenceEntitySource};
export type {ReferenceEntitySource};
