import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Source, Attribute} from '../../../models';
import {
  DefaultValueOperation,
  isDefaultValueOperation,
  ReplacementOperation,
  isReplacementOperation,
  CollectionSeparator,
} from '../common';

type ReferenceEntityCollectionOperations = {
  default_value?: DefaultValueOperation;
  replacement?: ReplacementOperation;
};

type ReferenceEntityCollectionCodeSelection = {
  type: 'code';
  separator: CollectionSeparator;
};

type ReferenceEntityCollectionAttributeSelection = {
  type: 'attribute';
  separator: CollectionSeparator;
  attribute_identifier: string;
  attribute_type: string;
  reference_entity_code: string;
  locale: LocaleReference;
  channel: ChannelReference;
};

type ReferenceEntityCollectionSelection =
  | ReferenceEntityCollectionCodeSelection
  | ReferenceEntityCollectionAttributeSelection;

type ReferenceEntityCollectionSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: ReferenceEntityCollectionOperations;
  selection: ReferenceEntityCollectionSelection;
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

const isReferenceEntityCollectionSelection = (selection: any): selection is ReferenceEntityCollectionSelection => {
  if (!('type' in selection) || !('separator' in selection)) return false;

  return 'code' === selection.type || 'attribute' === selection.type;
};

const isDefaultReferenceEntityCollectionSelection = (selection?: ReferenceEntityCollectionSelection): boolean =>
  'code' === selection?.type;

const isReferenceEntityCollectionOperations = (operations: Object): operations is ReferenceEntityCollectionOperations =>
  Object.entries(operations).every(([type, operation]) => {
    switch (type) {
      case 'default_value':
        return isDefaultValueOperation(operation);
      case 'replacement':
        return isReplacementOperation(operation);
      default:
        return false;
    }
  });

const isReferenceEntityCollectionSource = (source: Source): source is ReferenceEntityCollectionSource =>
  isReferenceEntityCollectionSelection(source.selection) && isReferenceEntityCollectionOperations(source.operations);

export type {
  ReferenceEntityCollectionSource,
  ReferenceEntityCollectionSelection,
  ReferenceEntityCollectionAttributeSelection,
};
export {
  getDefaultReferenceEntityCollectionSource,
  isDefaultReferenceEntityCollectionSelection,
  isReferenceEntityCollectionSource,
};
