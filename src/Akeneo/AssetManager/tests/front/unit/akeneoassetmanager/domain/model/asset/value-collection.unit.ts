import {createValueCollection, generateKey} from 'akeneoassetmanager/domain/model/asset/value-collection';
import {createValue} from 'akeneoassetmanager/domain/model/asset/value';
import {denormalizeAttributeIdentifier} from 'akeneoassetmanager/domain/model/attribute/identifier';
import {denormalize as denormalizeTextData} from 'akeneoassetmanager/domain/model/asset/data/text';
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
const niceDescription = denormalizeTextData('nice description');
const awesomeName = denormalizeTextData('awesome name');
const descriptionEnUsValue = createValue(description, null, enUS, niceDescription);
const descriptionFrFrValue = createValue(description, null, frFR, niceDescription);
const name = denormalizeTextAttribute({
  ...normalizedDescription,
  code: 'name',
  value_per_channel: true,
  value_per_locale: false,
});
const nameMobileValue = createValue(name, mobile, null, awesomeName);
const nameEcommerceValue = createValue(name, ecommerce, null, awesomeName);

describe('akeneo > asset family > domain > model > asset --- value collection', () => {
  test('I can create a new value collection', () => {
    expect(createValueCollection([descriptionEnUsValue]).normalize()).toEqual([
      {
        attribute: {
          code: 'description',
          asset_family_identifier: 'designer',
          identifier: 'description_1234',
          is_required: true,
          is_rich_text_editor: false,
          is_textarea: false,
          labels: {en_US: 'Description'},
          max_length: 0,
          order: 0,
          regular_expression: null,
          type: 'text',
          validation_rule: 'email',
          value_per_channel: false,
          value_per_locale: true,
        },
        channel: null,
        data: 'nice description',
        locale: 'en_US',
      },
    ]);
  });

  test('I cannot create an invalid value collection', () => {
    expect(() => {
      createValueCollection([descriptionEnUsValue, 'name']);
    }).toThrowError('ValueCollection expect only Value objects as argument');
  });

  test('I can normalize a new value collection', () => {
    expect(createValueCollection([descriptionEnUsValue, descriptionFrFrValue]).normalizeMinimal()).toEqual([
      {attribute: 'description_1234', channel: null, data: 'nice description', locale: 'en_US'},
      {attribute: 'description_1234', channel: null, data: 'nice description', locale: 'fr_FR'},
    ]);
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
      createValueCollection([
        descriptionEnUsValue,
        descriptionFrFrValue,
        nameMobileValue,
        nameEcommerceValue,
      ]).getValuesForChannelAndLocale(ecommerce, enUS)
    ).toEqual([descriptionEnUsValue, nameEcommerceValue]);
  });
});
