import {uuid} from 'akeneo-design-system';
import {ChannelCode, LocaleCode} from '@akeneo-pim-community/shared';
import {AssociationType, Source} from '../../../models';

const availableSeparators = {',': 'comma', ';': 'semicolon', '|': 'pipe'};
type CollectionSeparator = keyof typeof availableSeparators;
type AssociatedEntityType = 'products' | 'product_models' | 'groups';

type ProductsOrProductModelsSelection = {
  type: 'label';
  entity_type: 'products' | 'product_models';
  separator: CollectionSeparator;
  locale: LocaleCode;
  channel: ChannelCode;
};

type SimpleAssociationTypeSelection =
  | {
      type: 'code';
      entity_type: AssociatedEntityType;
      separator: CollectionSeparator;
    }
  | ProductsOrProductModelsSelection
  | {
      type: 'label';
      entity_type: 'groups';
      separator: CollectionSeparator;
      locale: LocaleCode;
    };

const isCollectionSeparator = (separator: unknown): separator is CollectionSeparator =>
  typeof separator === 'string' && separator in availableSeparators;

const isEntityType = (entityType: unknown): entityType is AssociatedEntityType =>
  typeof entityType === 'string' &&
  (entityType === 'products' || entityType === 'product_models' || entityType === 'groups');

const isProductOrProductModelSelection = (
  selection: SimpleAssociationTypeSelection
): selection is ProductsOrProductModelsSelection =>
  selection.entity_type === 'products' || selection.entity_type === 'product_models';

const isSimpleAssociationTypeSelection = (selection: any): selection is SimpleAssociationTypeSource =>
  'type' in selection &&
  'entity_type' in selection &&
  isEntityType(selection.entity_type) &&
  (selection.type === 'code' ||
    (isProductOrProductModelSelection(selection) &&
      selection.type === 'label' &&
      'locale' in selection &&
      'channel' in selection) ||
    (selection.entity_type === 'groups' && selection.type === 'label' && 'locale' in selection)) &&
  'separator' in selection &&
  isCollectionSeparator(selection.separator);

const getDefaultSimpleAssociationTypeSelection = (): SimpleAssociationTypeSelection => ({
  type: 'code',
  separator: ',',
  entity_type: 'products',
});

const isDefaultSimpleAssociationTypeSelection = (selection?: SimpleAssociationTypeSelection): boolean =>
  'code' === selection?.type && ',' === selection?.separator && 'products' === selection?.entity_type;

type SimpleAssociationTypeSource = {
  uuid: string;
  code: string;
  type: 'association_type';
  locale: null;
  channel: null;
  operations: {};
  selection: SimpleAssociationTypeSelection;
};

const getDefaultSimpleAssociationTypeSource = (associationType: AssociationType): SimpleAssociationTypeSource => ({
  uuid: uuid(),
  code: associationType.code,
  type: 'association_type',
  locale: null,
  channel: null,
  operations: {},
  selection: getDefaultSimpleAssociationTypeSelection(),
});

const isSimpleAssociationTypeSource = (source: Source): source is SimpleAssociationTypeSource =>
  source.type === 'association_type' && isSimpleAssociationTypeSelection(source.selection);

export type {SimpleAssociationTypeSource, SimpleAssociationTypeSelection};
export {
  availableSeparators,
  getDefaultSimpleAssociationTypeSource,
  isDefaultSimpleAssociationTypeSelection,
  isSimpleAssociationTypeSource,
  isCollectionSeparator,
  isEntityType,
  isProductOrProductModelSelection,
};
