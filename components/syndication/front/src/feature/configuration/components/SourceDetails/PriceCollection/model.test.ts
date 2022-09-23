import {
  getDefaultPriceCollectionSource,
  isPriceCollectionSelection,
  isPriceCollectionSource,
  PriceCollectionSource,
} from './model';

test("it tells if it's a price selection", () => {
  expect(isPriceCollectionSelection({separator: ';', type: 'amount'})).toBe(true);
  expect(isPriceCollectionSelection({separator: ',', type: 'currency_code'})).toBe(true);
  expect(isPriceCollectionSelection({separator: '|', type: 'currency_label', locale: 'fr_FR'})).toBe(true);
  expect(isPriceCollectionSelection({separator: ';', type: 'amount', currencies: ['EUR']})).toBe(true);
  expect(isPriceCollectionSelection({separator: 'wrong_separator', type: 'amount'})).toBe(false);
  expect(isPriceCollectionSelection({separator: ';', type: 'wrong_type'})).toBe(false);
  expect(isPriceCollectionSelection({separator: ';', type: 'amount', currencies: 'EUR'})).toBe(false);
});

test("it tells if it's a price collection source", () => {
  const source: PriceCollectionSource = {
    uuid: 'test_id',
    code: 'test_code',
    type: 'attribute',
    locale: null,
    channel: null,
    operations: {},
    selection: {type: 'amount', separator: ','},
  };

  expect(isPriceCollectionSource(source)).toBe(true);

  expect(
    isPriceCollectionSource({
      ...source,
      operations: {
        default_value: {
          type: 'default_value',
          value: 'a default value',
        },
      },
    })
  ).toEqual(true);

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

  const target = {
    type: 'string' as const,
    name: 'price',
    required: false,
  };

  expect(getDefaultPriceCollectionSource(attribute, target, CHANNEL_CODE, LOCALE_CODE)).toStrictEqual({
    uuid: expect.any(String),
    code: attribute.code,
    type: 'attribute',
    locale: LOCALE_CODE,
    channel: CHANNEL_CODE,
    operations: {},
    selection: {type: 'amount', separator: ','},
  });
});
