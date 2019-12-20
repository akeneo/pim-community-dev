import {
  getValuesForChannelAndLocale,
  getValueFilter,
  getValue,
} from 'akeneoassetmanager/domain/model/asset/value-collection';
import {generateValueKey} from 'akeneoassetmanager/domain/model/asset/list-asset';
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
const descriptionAttribute = denormalizeTextAttribute(normalizedDescription);
const description = 'description';
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
const name = 'name';
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
const nameNoChannelValue = {
  attribute: name,
  channel: null,
  locale: null,
  data: awesomeName,
};
const nameValue = {
  attribute: name,
  channel: ecommerce,
  locale: enUS,
  data: awesomeName,
};
const descriptionEditionValue = {
  attribute: descriptionAttribute,
  channel: null,
  locale: enUS,
  data: niceDescription,
};
const editionValueCollection = [descriptionEditionValue];

describe('akeneo > asset family > domain > model > asset --- value collection', () => {
  test('I can get a Value filter for an identifier and a locale & scope', () => {
    const filter = getValueFilter('description_1234', null, 'en_US');
    expect(filter(descriptionEditionValue)).toBe(true);
    expect(filter(descriptionFrFrValue)).toBe(false);
  });

  test('I can minimal normalize a new value collection', () => {
    expect([descriptionEnUsValue, descriptionFrFrValue]).toEqual([descriptionEnUsValue, descriptionFrFrValue]);
  });

  test('I can normalize a new value collection', () => {
    expect([descriptionEnUsValue, descriptionFrFrValue]).toEqual([descriptionEnUsValue, descriptionFrFrValue]);
  });

  test('I can generate a value key', () => {
    expect(generateValueKey(nameValue)).toEqual('name_ecommerce_en_US');
    expect(generateValueKey(nameEcommerceValue)).toEqual('name_ecommerce');
    expect(generateValueKey(descriptionEnUsValue)).toEqual('description_en_US');
    expect(generateValueKey(nameNoChannelValue)).toEqual('name');
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

  test('I can get a Value from a Value collection', () => {
    expect(getValue(editionValueCollection, 'description_1234', null, 'en_US')).toEqual(descriptionEditionValue);
  });
});
