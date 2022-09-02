import {getDefaultPriceTarget, isPriceTarget, PriceTarget} from './model';
import {TextTarget} from '../Text/model';

test('it returns true if it is a price target', () => {
  const priceTarget: PriceTarget = {
    code: 'net_price',
    type: 'attribute',
    locale: null,
    channel: null,
    source_configuration: {
      decimal_separator: ',',
      currency: 'EUR',
    },
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
    attribute_type: 'pim_catalog_price_collection',
  };

  expect(isPriceTarget(priceTarget)).toBe(true);
});

test('it returns false if it is not a price target', () => {
  const textTarget: TextTarget = {
    code: 'text',
    type: 'attribute',
    locale: null,
    channel: null,
    source_configuration: null,
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
    attribute_type: 'pim_catalog_text',
  };

  expect(isPriceTarget(textTarget)).toBe(false);
});

test('it returns a default price target', () => {
  const attribute = {
    code: 'net_price',
    type: 'pim_catalog_price_collection',
    labels: {},
    scopable: false,
    localizable: false,
    is_locale_specific: false,
    available_locales: [],
    decimals_allowed: true,
  };

  const priceTarget: PriceTarget = {
    code: 'net_price',
    type: 'attribute',
    locale: null,
    channel: null,
    source_configuration: {
      decimal_separator: '.',
      currency: null,
    },
    action_if_not_empty: 'set',
    action_if_empty: 'skip',
    attribute_type: 'pim_catalog_price_collection',
  };

  expect(getDefaultPriceTarget(attribute, null, null)).toEqual(priceTarget);
});
