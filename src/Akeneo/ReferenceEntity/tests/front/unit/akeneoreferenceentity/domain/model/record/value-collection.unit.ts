import {createValueCollection} from 'akeneoreferenceentity/domain/model/record/value-collection';
import {createValue} from 'akeneoreferenceentity/domain/model/record/value';
import {denormalizeAttribute} from 'akeneoreferenceentity/domain/model/attribute/attribute';
import {denormalizeChannelReference} from 'akeneoreferenceentity/domain/model/channel-reference';
import {denormalizeLocaleReference} from 'akeneoreferenceentity/domain/model/locale-reference';
import {denormalize as denormalizeTextData} from 'akeneoreferenceentity/domain/model/record/data/text';

const normalizedDescription = {
  identifier: 'description_1234',
  reference_entity_identifier: 'designer',
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
const description = denormalizeAttribute(normalizedDescription);
const ecommerce = denormalizeChannelReference('ecommerce');
const mobile = denormalizeChannelReference('mobile');
const enUS = denormalizeLocaleReference('en_US');
const frFR = denormalizeLocaleReference('fr_FR');
const niceDescription = denormalizeTextData('nice description');
const awesomeName = denormalizeTextData('awesome name');
const descriptionEnUsValue = createValue(description, denormalizeChannelReference(null), enUS, niceDescription);
const descriptionFrFrValue = createValue(description, denormalizeChannelReference(null), frFR, niceDescription);
const name = denormalizeAttribute({
  ...normalizedDescription,
  code: 'name',
  value_per_channel: true,
  value_per_locale: false,
});
const nameMobileValue = createValue(name, mobile, denormalizeLocaleReference(null), awesomeName);
const nameEcommerceValue = createValue(name, ecommerce, denormalizeLocaleReference(null), awesomeName);

describe('akeneo > reference entity > domain > model > record --- value collection', () => {
  test('I can create a new value collection', () => {
    expect(createValueCollection([descriptionEnUsValue]).normalize()).toEqual([
      {
        attribute: {
          code: 'description',
          reference_entity_identifier: 'designer',
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
