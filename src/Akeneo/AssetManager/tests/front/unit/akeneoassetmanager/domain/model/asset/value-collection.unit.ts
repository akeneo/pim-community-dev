import {
  getValuesForChannelAndLocale,
  generateKey,
  getValueFilter,
} from 'akeneoassetmanager/domain/model/asset/value-collection';
import {denormalizeAttributeIdentifier} from 'akeneoassetmanager/domain/model/attribute/identifier';
import {denormalize as denormalizeTextAttribute} from 'akeneoassetmanager/domain/model/attribute/type/text';

const normalizedDescription = {
  identifier: 'description_1234',
  asset_family_identifier: 'designer',
  code: 'description',
  labels: {en_US: 'Description'},
  type: 'text',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  max_length: 0,
  is_textarea: false,
  is_rich_text_editor: false,
  validation_rule: 'email',
  regular_expression: null,
};
const description = denormalizeTextAttribute(normalizedDescription);
const ecommerce = 'ecommerce';
const mobile = 'mobile';
const enUS = 'en_US';
const frFR = 'fr_FR';
const niceDescription = 'nice description';
const awesomeName = 'awesome name';
const descriptionEnUsValue = {
  attribute: description,
  channel: null,
  locale: enUS,
  data: niceDescription,
};
const descriptionFrFrValue = {
  attribute: description,
  channel: null,
  locale: frFR,
  data: niceDescription,
};
const name = denormalizeTextAttribute({
  ...normalizedDescription,
  code: 'name',
  value_per_channel: true,
  value_per_locale: false,
});
const nameMobileValue = {
  attribute: name,
  channel: mobile,
  locale: null,
  data: awesomeName,
};
const nameEcommerceValue = {
  attribute: name,
  channel: ecommerce,
  locale: null,
  data: awesomeName,
};

describe('akeneo > asset family > domain > model > asset --- value collection', () => {
  test('I can get a Value filter for an identifier and a locale & scope', () => {
    const filter = getValueFilter('description_1234', null, 'en_US');
    expect(filter(descriptionEnUsValue)).toBe(true);
    expect(filter(descriptionFrFrValue)).toBe(false);
  });

  test('I can minimal normalize a new value collection', () => {
    expect([descriptionEnUsValue, descriptionFrFrValue]).toEqual([descriptionEnUsValue, descriptionFrFrValue]);
  });

  test('I can normalize a new value collection', () => {
    expect([descriptionEnUsValue, descriptionFrFrValue]).toEqual([descriptionEnUsValue, descriptionFrFrValue]);
  });

  test('I can generate a value key', () => {
    expect(generateKey(denormalizeAttributeIdentifier('description'), 'ecommerce', 'en_US')).toEqual(
      'description_ecommerce_en_US'
    );
    expect(generateKey(denormalizeAttributeIdentifier('description'), 'ecommerce', null)).toEqual(
      'description_ecommerce'
    );
    expect(generateKey(denormalizeAttributeIdentifier('description'), null, 'en_US')).toEqual('description_en_US');
    expect(generateKey(denormalizeAttributeIdentifier('description'), null, null)).toEqual('description');
  });

  test('I cannot create an invalid value collection', () => {
    expect(
      getValuesForChannelAndLocale(
        [descriptionEnUsValue, descriptionFrFrValue, nameMobileValue, nameEcommerceValue],
        ecommerce,
        enUS
      )
    ).toEqual([descriptionEnUsValue, nameEcommerceValue]);
  });
});
