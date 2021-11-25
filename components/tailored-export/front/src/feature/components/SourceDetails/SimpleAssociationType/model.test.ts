import {
  getDefaultSimpleAssociationTypeSource,
  isCollectionSeparator,
  isEntityType,
  isProductOrProductModelSelection,
  isSimpleAssociationTypeSource,
  SimpleAssociationTypeSource,
} from './model';
import {AssociationType} from '../../../models';
import {getDefaultEnabledSource} from '../Enabled/model';

jest.mock('akeneo-design-system/lib/shared/uuid', () => ({
  uuid: () => '276b6361-badb-48a1-98ef-d75baa235148',
}));

const associationType: AssociationType = {
  code: 'X_SELL',
  labels: {},
  is_quantified: false,
};

test('it can get the default simple association source', () => {
  expect(getDefaultSimpleAssociationTypeSource(associationType)).toEqual({
    uuid: '276b6361-badb-48a1-98ef-d75baa235148',
    type: 'association_type',
    code: 'X_SELL',
    locale: null,
    channel: null,
    operations: {},
    selection: {
      type: 'code',
      separator: ',',
      entity_type: 'products',
    },
  } as SimpleAssociationTypeSource);
});

test('it returns if source is simple association source', () => {
  expect(
    isSimpleAssociationTypeSource({
      uuid: '276b6361-badb-48a1-98ef-d75baa235148',
      type: 'association_type',
      code: 'X_SELL',
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
    isSimpleAssociationTypeSource({
      uuid: '276b6361-badb-48a1-98ef-d75baa235148',
      type: 'association_type',
      code: 'X_SELL',
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

  expect(
    isSimpleAssociationTypeSource({
      uuid: '276b6361-badb-48a1-98ef-d75baa235148',
      type: 'association_type',
      code: 'X_SELL',
      locale: null,
      channel: null,
      operations: {},
      selection: {
        type: 'label',
        separator: ',',
        locale: 'en_US',
        entity_type: 'groups',
      },
    })
  ).toBe(true);

  expect(
    isSimpleAssociationTypeSource({
      uuid: '276b6361-badb-48a1-98ef-d75baa235148',
      type: 'association_type',
      code: 'X_SELL',
      locale: null,
      channel: null,
      operations: {},
      selection: {
        type: 'label',
        separator: ',',
        entity_type: 'products',
      },
    } as any)
  ).toBe(false);
  expect(isSimpleAssociationTypeSource(getDefaultEnabledSource())).toBe(false);
});

test('it returns if collection separator is valid', () => {
  expect(isCollectionSeparator(',')).toBe(true);
  expect(isCollectionSeparator(';')).toBe(true);
  expect(isCollectionSeparator('|')).toBe(true);
  expect(isCollectionSeparator('٫')).toBe(false);
});

test('it returns if the entity type selection a product or a product model', () => {
  expect(isProductOrProductModelSelection({type: 'code', separator: ',', entity_type: 'products'})).toBe(true);
  expect(isProductOrProductModelSelection({type: 'code', separator: ',', entity_type: 'product_models'})).toBe(true);
  expect(isProductOrProductModelSelection({type: 'code', separator: ',', entity_type: 'groups'})).toBe(false);

  expect(
    isProductOrProductModelSelection({
      type: 'label',
      separator: ',',
      locale: 'en_US',
      entity_type: 'products',
      channel: 'ecommerce',
    })
  ).toBe(true);

  expect(
    isProductOrProductModelSelection({
      type: 'label',
      separator: ',',
      locale: 'en_US',
      entity_type: 'product_models',
      channel: 'ecommerce',
    })
  ).toBe(true);

  expect(
    isProductOrProductModelSelection({
      type: 'label',
      separator: ',',
      locale: 'en_US',
      entity_type: 'groups',
    })
  ).toBe(false);
});

test('it returns if entity type is valid', () => {
  expect(isEntityType('products')).toBe(true);
  expect(isEntityType('product_models')).toBe(true);
  expect(isEntityType('groups')).toBe(true);

  expect(isEntityType('association')).toBe(false);
});
