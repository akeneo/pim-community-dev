import {uuid} from 'akeneo-design-system';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Source, Attribute} from '../../../models';
import {
  CodeLabelSelection,
  DefaultValueOperation,
  isCodeLabelSelection,
  isDefaultValueOperation,
  isReplacementOperation,
  ReplacementOperation,
} from '../common';

type ReferenceEntityOperations = {
  default_value?: DefaultValueOperation;
  replacement?: ReplacementOperation;
};

type ReferenceEntityAttributeSelection = {
  type: 'attribute';
  attribute_identifier: string;
  attribute_type: string;
  reference_entity_code: string;
  locale: LocaleReference;
  channel: ChannelReference;
};

type ReferenceEntitySelection = CodeLabelSelection | ReferenceEntityAttributeSelection;

type ReferenceEntitySource = {
  uuid: string;
  code: string;
  type: 'attribute';
  locale: LocaleReference;
  channel: ChannelReference;
  operations: ReferenceEntityOperations;
  selection: ReferenceEntitySelection;
};

const isReferenceEntityAttributeSelection = (selection: any): selection is ReferenceEntityAttributeSelection =>
  'attribute' === selection.type &&
  'string' === typeof selection.attribute_identifier &&
  'string' === typeof selection.attribute_type &&
  'string' === typeof selection.reference_entity_code;

const isReferenceEntitySelection = (selection: any): selection is ReferenceEntitySelection =>
  isCodeLabelSelection(selection) || isReferenceEntityAttributeSelection(selection);

const isDefaultReferenceEntitySelection = (selection?: ReferenceEntitySelection): boolean => 'code' === selection?.type;

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
  selection: {type: 'code'},
});

const isReferenceEntityOperations = (operations: Object): operations is ReferenceEntityOperations =>
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

const isReferenceEntitySource = (source: Source): source is ReferenceEntitySource =>
  isReferenceEntitySelection(source.selection) && isReferenceEntityOperations(source.operations);

export {isReferenceEntitySource, getDefaultReferenceEntitySource, isDefaultReferenceEntitySelection};
export type {ReferenceEntitySource, ReferenceEntitySelection, ReferenceEntityAttributeSelection};
