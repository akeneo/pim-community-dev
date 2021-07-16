import {
  getDefaultQuantifiedAssociationTypeSource,
  isCollectionSeparator,
  isEntityType,
  isQuantifiedAssociationTypeSource,
  QuantifiedAssociationTypeSource,
} from './model';
import {AssociationType} from '../../../models';
import {getDefaultEnabledSource} from '../Enabled/model';

jest.mock('akeneo-design-system/lib/shared/uuid', () => ({
  uuid: () => '276b6361-badb-48a1-98ef-d75baa235148',
}));

const associationType: AssociationType = {
  code: 'PACK',
  labels: {},
  is_quantified: true,
};

test('it can get the default quantified association source', () => {
  expect(getDefaultQuantifiedAssociationTypeSource(associationType)).toEqual({
    uuid: '276b6361-badb-48a1-98ef-d75baa235148',
    type: 'association_type',
    code: 'PACK',
    locale: null,
    channel: null,
    operations: {},
    selection: {
      type: 'code',
      separator: ',',
      entity_type: 'products',
    },
  } as QuantifiedAssociationTypeSource);
});

test('it tells if source is quantified association source', () => {
  expect(
    isQuantifiedAssociationTypeSource({
      uuid: '276b6361-badb-48a1-98ef-d75baa235148',
      type: 'association_type',
      code: 'PACK',
      locale: null,
      channel: null,
      operations: {},
      selection: {
        type: 'code',
        separator: ',',
        entity_type: 'products',
      },
    })
  ).toBe(true);

  expect(
    isQuantifiedAssociationTypeSource({
      uuid: '276b6361-badb-48a1-98ef-d75baa235148',
      type: 'association_type',
      code: 'PACK',
      locale: null,
      channel: null,
      operations: {},
      selection: {
        type: 'label',
        separator: ',',
        locale: 'en_US',
        channel: 'ecommerce',
        entity_type: 'products',
      },
    })
  ).toBe(true);

  expect(isQuantifiedAssociationTypeSource(getDefaultEnabledSource())).toBe(false);
});

test('it tells if collection separator is valid', () => {
  expect(isCollectionSeparator(',')).toBe(true);
  expect(isCollectionSeparator(';')).toBe(true);
  expect(isCollectionSeparator('|')).toBe(true);
  expect(isCollectionSeparator('٫')).toBe(false);
});

test('it tells if entity type is valid', () => {
  expect(isEntityType('products')).toBe(true);
  expect(isEntityType('product_models')).toBe(true);

  expect(isEntityType('groups')).toBe(false);
  expect(isEntityType('association')).toBe(false);
});
