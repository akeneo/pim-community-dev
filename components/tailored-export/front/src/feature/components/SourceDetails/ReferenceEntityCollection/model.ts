import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Source, Attribute, ReferenceEntityAttribute} from '../../../models';
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

type ReferenceEntityCollectionNumberAttributeSelection = ReferenceEntityCollectionAttributeSelection & {
  attribute_type: 'number';
  decimal_separator: string;
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

const isReferenceEntityCollectionNumberAttributeSelection = (
  selection: any
): selection is ReferenceEntityCollectionNumberAttributeSelection =>
  'number' === selection.attribute_type &&
  'string' === typeof selection.decimal_separator &&
  isReferenceEntityCollectionAttributeSelection(selection);

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

const getDefaultReferenceEntityCollectionAttributeSelection = (
  attribute: ReferenceEntityAttribute,
  referenceEntityCode: string,
  channel: ChannelReference,
  locale: LocaleReference
) => {
  const selection: ReferenceEntityCollectionAttributeSelection = {
    type: 'attribute',
    separator: ',',
    attribute_identifier: attribute.identifier,
    attribute_type: attribute.type,
    reference_entity_code: referenceEntityCode,
    channel,
    locale,
  };

  switch (attribute.type) {
    case 'text':
      return selection;
    case 'number':
      return {
        ...selection,
        decimal_separator: '.',
      };
    default:
      throw new Error(`Unsupported attribute type "${attribute.type}"`);
  }
};

export type {
  ReferenceEntityCollectionSource,
  ReferenceEntityCollectionSelection,
  ReferenceEntityCollectionAttributeSelection,
  ReferenceEntityCollectionNumberAttributeSelection,
};
export {
  getDefaultReferenceEntityCollectionAttributeSelection,
  isReferenceEntityCollectionNumberAttributeSelection,
  getDefaultReferenceEntityCollectionSource,
  isDefaultReferenceEntityCollectionSelection,
  isReferenceEntityCollectionSource,
};
