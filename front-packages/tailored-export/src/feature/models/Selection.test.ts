import {getDefaultSelectionByAttribute, isCollectionSeparator} from './Selection';
import {Attribute} from './Attribute';

const getAttribute = (type: string): Attribute => ({
  code: 'nice_attribute',
  type,
  labels: {},
  scopable: false,
  localizable: false,
});

test('it returns default selection by attribute type', () => {
  expect(getDefaultSelectionByAttribute(getAttribute('pim_catalog_text'))).toEqual({type: 'code'});
  expect(getDefaultSelectionByAttribute(getAttribute('pim_catalog_price_collection'))).toEqual({type: 'amount'});
  expect(getDefaultSelectionByAttribute(getAttribute('pim_catalog_price_collection'))).toEqual({type: 'amount'});
  expect(getDefaultSelectionByAttribute(getAttribute('pim_catalog_multiselect'))).toEqual({
    type: 'code',
    separator: ',',
  });
});

test('it can tell if something is a valid selection separator', () => {
  expect(isCollectionSeparator(',')).toEqual(true);
  expect(isCollectionSeparator(';')).toEqual(true);
  expect(isCollectionSeparator('|')).toEqual(true);
  expect(isCollectionSeparator('coucou')).toEqual(false);
  expect(isCollectionSeparator('')).toEqual(false);
  expect(isCollectionSeparator('.')).toEqual(false);
});
