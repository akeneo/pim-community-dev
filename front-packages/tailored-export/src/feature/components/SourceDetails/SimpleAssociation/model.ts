import {uuid} from 'akeneo-design-system';
import {LocaleCode} from '@akeneo-pim-community/shared';
import {AssociationType, Source} from '../../../models';

const availableSeparators = {',': 'comma', ';': 'semicolon', '|': 'pipe'};
type CollectionSeparator = keyof typeof availableSeparators;
type AssociationEntityType = 'products' | 'product_models' | 'groups';

type SimpleAssociationSelection =
  | {
  type: 'code';
  entity_type: AssociationEntityType;
  separator: CollectionSeparator;
}
  | {
  type: 'label';
  entity_type: AssociationEntityType;
  separator: CollectionSeparator;
  locale: LocaleCode;
};

const isCollectionSeparator = (separator: unknown): separator is CollectionSeparator =>
  typeof separator === 'string' && separator in availableSeparators;

const isEntityType = (entityType: unknown): entityType is AssociationEntityType =>
  typeof entityType === 'string' && (entityType === 'products' || entityType === 'product_models' || entityType === 'groups');

const isSimpleAssociationSelection = (selection: any): selection is SimpleAssociationSelection =>
  'type' in selection &&
  (selection.type === 'code' || (selection.type === 'label' && 'locale' in selection)) &&
  'separator' in selection &&
  isCollectionSeparator(selection.separator) &&
  'entity_type' in selection &&
  isEntityType(selection.entity_type)
;

type SimpleAssociationSource = {
  uuid: string;
  code: string;
  type: 'association';
  locale: null;
  channel: null;
  operations: {};
  selection: SimpleAssociationSelection;
};

const getDefaultSimpleAssociationTypeSource = (associationType: AssociationType): SimpleAssociationSource => ({
  uuid: uuid(),
  code: associationType.code,
  type: 'association',
  locale: null,
  channel: null,
  operations: {},
  selection: {type: 'code', separator: ',', entity_type: 'products'},
});

const isSimpleAssociationSource = (source: Source): source is SimpleAssociationSource =>
  source.type === 'association' && isSimpleAssociationSelection(source.selection);

export type {SimpleAssociationSource, SimpleAssociationSelection};
export {availableSeparators, getDefaultSimpleAssociationTypeSource, isSimpleAssociationSource, isCollectionSeparator, isEntityType};
