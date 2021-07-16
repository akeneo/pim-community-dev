import {uuid} from 'akeneo-design-system';
import {ChannelCode, LocaleCode} from '@akeneo-pim-community/shared';
import {AssociationType, Source} from '../../../models';

const availableSeparators = {',': 'comma', ';': 'semicolon', '|': 'pipe'};
type CollectionSeparator = keyof typeof availableSeparators;
type AssociatedEntityType = 'products' | 'product_models';

type QuantifiedAssociationTypeSelection =
  | {
      type: 'code';
      entity_type: AssociatedEntityType;
      separator: CollectionSeparator;
    }
  | {
      type: 'quantity';
      entity_type: AssociatedEntityType;
      separator: CollectionSeparator;
    }
  | {
      type: 'label';
      entity_type: 'products' | 'product_models';
      separator: CollectionSeparator;
      locale: LocaleCode;
      channel: ChannelCode;
    };

const isCollectionSeparator = (separator: unknown): separator is CollectionSeparator =>
  typeof separator === 'string' && separator in availableSeparators;

const isEntityType = (entityType: unknown): entityType is AssociatedEntityType =>
  typeof entityType === 'string' && (entityType === 'products' || entityType === 'product_models');

const isQuantifiedAssociationTypeSelection = (selection: any): selection is QuantifiedAssociationTypeSource =>
  'type' in selection &&
  (selection.type === 'code' ||
    selection.type === 'quantity' ||
    (selection.type === 'label' && 'locale' in selection && 'channel' in selection)) &&
  'separator' in selection &&
  isCollectionSeparator(selection.separator) &&
  'entity_type' in selection &&
  isEntityType(selection.entity_type);

type QuantifiedAssociationTypeSource = {
  uuid: string;
  code: string;
  type: 'association_type';
  locale: null;
  channel: null;
  operations: {};
  selection: QuantifiedAssociationTypeSelection;
};

const getDefaultQuantifiedAssociationTypeSource = (
  associationType: AssociationType
): QuantifiedAssociationTypeSource => ({
  uuid: uuid(),
  code: associationType.code,
  type: 'association_type',
  locale: null,
  channel: null,
  operations: {},
  selection: {type: 'code', separator: ',', entity_type: 'products'},
});

const isQuantifiedAssociationTypeSource = (source: Source): source is QuantifiedAssociationTypeSource =>
  source.type === 'association_type' && isQuantifiedAssociationTypeSelection(source.selection);

export type {QuantifiedAssociationTypeSource, QuantifiedAssociationTypeSelection};
export {
  availableSeparators,
  getDefaultQuantifiedAssociationTypeSource,
  isQuantifiedAssociationTypeSource,
  isCollectionSeparator,
  isEntityType,
};
