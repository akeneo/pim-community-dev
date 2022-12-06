import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Source, Attribute} from '../../../models';
import {
  DefaultValueOperation,
  isDefaultValueOperation,
  ReplacementOperation,
  isReplacementOperation,
  CollectionSeparator,
  CodeLabelCollectionSelection,
  isCodeLabelCollectionSelection,
} from '../common';

type ReferenceEntityCollectionOperations = {
  default_value?: DefaultValueOperation;
  replacement?: ReplacementOperation;
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

type ReferenceEntityCollectionSelection = CodeLabelCollectionSelection | ReferenceEntityCollectionAttributeSelection;

type ReferenceEntityCollectionSource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: ReferenceEntityCollectionOperations;
  selection: ReferenceEntityCollectionSelection;
};

const isReferenceEntityCollectionAttributeSelection = (
  selection: any
): selection is ReferenceEntityCollectionAttributeSelection =>
  'attribute' === selection.type &&
  'string' === typeof selection.attribute_identifier &&
  'string' === typeof selection.attribute_type &&
  'string' === typeof selection.reference_entity_code &&
  'string' === typeof selection.separator;

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

const isReferenceEntityCollectionSelection = (selection: any): selection is ReferenceEntityCollectionSelection =>
  isCodeLabelCollectionSelection(selection) || isReferenceEntityCollectionAttributeSelection(selection);

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
