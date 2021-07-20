import {getDefaultPriceCollectionSource, isPriceCollectionSelection, isPriceCollectionSource} from './model';

test("it tells if it's a price selection", () => {
  expect(isPriceCollectionSelection({separator: ';', type: 'amount'})).toBe(true);
  expect(isPriceCollectionSelection({separator: ',', type: 'currency_code'})).toBe(true);
  expect(isPriceCollectionSelection({separator: '|', type: 'currency_label', locale: 'fr_FR'})).toBe(true);
  expect(isPriceCollectionSelection({separator: 'wrong_separator', type: 'amount'})).toBe(false);
  expect(isPriceCollectionSelection({separator: ';', type: 'wrong_type'})).toBe(false);
});

test("it tells if it's a price collection source", () => {
  expect(
    isPriceCollectionSource({
      uuid: 'test_id',
      code: 'test_code',
      type: 'attribute',
      locale: null,
      channel: null,
      operations: {},
      selection: {type: 'amount', separator: ','},
    })
  ).toBe(true);
  expect(isPriceCollectionSelection({})).toBe(false);
});

test('it initializes a default price collection source', () => {
  const CHANNEL_CODE = 'ecommerce';
  const LOCALE_CODE = 'fr_FR';

  const attribute = {
    code: 'price',
    type: 'pim_catalog_price_collection',
    labels: {},
    scopable: false,
    localizable: false,
    is_locale_specific: false,
    available_locales: [],
  };
  expect(getDefaultPriceCollectionSource(attribute, CHANNEL_CODE, LOCALE_CODE)).toStrictEqual({
    uuid: expect.any(String),
    code: attribute.code,
    type: 'attribute',
    locale: LOCALE_CODE,
    channel: CHANNEL_CODE,
    operations: {},
    selection: {type: 'amount', separator: ','},
  });
});
