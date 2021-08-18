import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Source, Attribute} from '../../../models';
import {CodeLabelCollectionSelection, isCodeLabelCollectionSelection} from '../common/CodeLabelCollectionSelector';
import {DefaultValueOperation, isDefaultValueOperation} from '../common';

type ReferenceEntityCollectionOperations = {
  default_value?: DefaultValueOperation;
};

type ReferenceEntityCollectionSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: ReferenceEntityCollectionOperations;
  selection: CodeLabelCollectionSelection;
};

const getDefaultReferenceEntityCollectionSource = (
  attribute: Attribute,
  channel: ChannelReference,
  locale: LocaleReference
): ReferenceEntityCollectionSource => ({
  uuid: uuid(),
  code: attribute.code,
  type: 'attribute',
  locale,
  channel,
  operations: {},
  selection: {type: 'code', separator: ','},
});

const isReferenceEntityCollectionOperations = (operations: Object): operations is ReferenceEntityCollectionOperations =>
  Object.entries(operations).every(([type, operation]) => {
    switch (type) {
      case 'default_value':
        return isDefaultValueOperation(operation);
      default:
        return false;
    }
  });

const isReferenceEntityCollectionSource = (source: Source): source is ReferenceEntityCollectionSource =>
  isCodeLabelCollectionSelection(source.selection) && isReferenceEntityCollectionOperations(source.operations);

export type {ReferenceEntityCollectionSource};
export {getDefaultReferenceEntityCollectionSource, isReferenceEntityCollectionSource};
