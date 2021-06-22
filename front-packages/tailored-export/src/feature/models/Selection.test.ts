import {
  availableDateFormats,
  getDefaultSelectionByAttribute,
  getDefaultSelectionByProperty,
  isCollectionSeparator,
  isDateFormat,
} from './Selection';
import {Attribute} from './Attribute';

const getAttribute = (type: string): Attribute => ({
  code: 'nice_attribute',
  type,
  labels: {},
  scopable: false,
  localizable: false,
  is_locale_specific: false,
  available_locales: [],
});

test('it returns default selection by attribute type', () => {
  expect(getDefaultSelectionByAttribute(getAttribute('pim_catalog_text'))).toEqual({type: 'code'});
  expect(getDefaultSelectionByAttribute(getAttribute('pim_catalog_price_collection'))).toEqual({type: 'amount'});
  expect(getDefaultSelectionByAttribute(getAttribute('pim_catalog_asset_collection'))).toEqual({
    type: 'code',
    separator: ',',
  });
  expect(getDefaultSelectionByAttribute(getAttribute('akeneo_reference_entity_collection'))).toEqual({
    type: 'code',
    separator: ',',
  });
  expect(getDefaultSelectionByAttribute(getAttribute('pim_catalog_multiselect'))).toEqual({
    type: 'code',
    separator: ',',
  });
  expect(getDefaultSelectionByAttribute(getAttribute('pim_catalog_date'))).toEqual({
    format: 'yyyy-mm-dd',
  });
});

test('it returns default selection by property name', () => {
  expect(getDefaultSelectionByProperty('categories')).toEqual({type: 'code', separator: ','});
  expect(getDefaultSelectionByProperty('family')).toEqual({type: 'code'});
});

test('it can tell if something is a valid selection separator', () => {
  expect(isCollectionSeparator(',')).toEqual(true);
  expect(isCollectionSeparator(';')).toEqual(true);
  expect(isCollectionSeparator('|')).toEqual(true);
  expect(isCollectionSeparator('coucou')).toEqual(false);
  expect(isCollectionSeparator('')).toEqual(false);
  expect(isCollectionSeparator('.')).toEqual(false);
});

test.each(availableDateFormats)('it can tell if "%s" is a valid date format', dateFormat => {
  expect(isDateFormat(dateFormat)).toEqual(true);
});

test('it can tell if something is an invalid date format', () => {
  expect(isDateFormat('.')).toEqual(false);
  expect(isDateFormat(' ')).toEqual(false);
  expect(isDateFormat('hello')).toEqual(false);
});
