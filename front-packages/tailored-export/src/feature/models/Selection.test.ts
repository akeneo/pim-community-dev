import {getDefaultSelectionByAttribute} from './Selection';
import {Attribute} from './Attribute';

test('it return default selection by attribute type', () => {
  const textAttribute: Attribute = {
    code: 'name',
    type: 'pim_catalog_text',
    labels: {},
    scopable: false,
    localizable: false,
  };

  const priceCollectionAttribute: Attribute = {
    code: 'name',
    type: 'pim_catalog_price_collection',
    labels: {},
    scopable: false,
    localizable: false,
  };

  expect(getDefaultSelectionByAttribute(textAttribute)).toEqual({type: 'code'});
  expect(getDefaultSelectionByAttribute(priceCollectionAttribute)).toEqual({type: 'amount'});
});
