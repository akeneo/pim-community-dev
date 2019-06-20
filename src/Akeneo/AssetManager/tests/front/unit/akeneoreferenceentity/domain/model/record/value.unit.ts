import {createValue} from 'akeneoreferenceentity/domain/model/record/value';
import {denormalize as denormalizeTextAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/text';
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
const normalizedWebsite = {
  identifier: 'website_1234',
  reference_entity_identifier: 'designer',
  code: 'website',
  labels: {en_US: 'Website'},
  type: 'text',
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: false,
  max_length: 0,
  is_textarea: false,
  is_rich_text_editor: false,
  validation_rule: 'url',
  regular_expression: null,
};
const description = denormalizeTextAttribute(normalizedDescription);
const website = denormalizeTextAttribute(normalizedWebsite);
const ecommerce = denormalizeChannelReference('ecommerce');
const enUS = denormalizeLocaleReference('en_US');
const data = denormalizeTextData('a nice description');

describe('akeneo > reference entity > domain > model > record --- value', () => {
  test('I can create a new value with a text data', () => {
    expect(createValue(description, denormalizeChannelReference(null), enUS, data).normalize()).toEqual({
      attribute: normalizedDescription,
      channel: null,
      data: 'a nice description',
      locale: 'en_US',
    });
  });

  test('I cannot create an invalid value', () => {
    expect(() => {
      createValue('description', ecommerce, enUS, data).normalize();
    }).toThrowError('Value expect ConcreteAttribute as attribute argument');
    expect(() => {
      createValue(description, 'ecommerce', enUS, data).normalize();
    }).toThrowError('Value expect ChannelReference as channel argument');
    expect(() => {
      createValue(description, ecommerce, 'enUS', data).normalize();
    }).toThrowError('Value expect LocaleReference as locale argument');
    expect(() => {
      createValue(description, ecommerce, enUS, 'data').normalize();
    }).toThrowError('Value expect ValueData as data argument');
    expect(() => {
      createValue(description, ecommerce, enUS, data).normalize();
    }).toThrowError('The value for attribute "description" should have an empty channel reference');
    expect(() => {
      createValue(description, denormalizeChannelReference(null), denormalizeLocaleReference(null), data).normalize();
    }).toThrowError('The value for attribute "description" should have a non empty locale reference');
    const name = denormalizeTextAttribute({
      ...normalizedDescription,
      code: 'name',
      value_per_channel: true,
      value_per_locale: false,
    });
    expect(() => {
      createValue(name, denormalizeChannelReference(null), denormalizeLocaleReference(null), data).normalize();
    }).toThrowError('The value for attribute "name" should have a non empty channel reference');
    expect(() => {
      createValue(name, ecommerce, enUS, data).normalize();
    }).toThrowError('The value for attribute "name" should have an empty locale reference');
  });

  test('I can set new data to a value', () => {
    expect(
      createValue(description, denormalizeChannelReference(null), enUS, data)
        .setData(denormalizeTextData('a new description!'))
        .normalize()
    ).toEqual({
      attribute: normalizedDescription,
      channel: null,
      data: 'a new description!',
      locale: 'en_US',
    });
  });

  test('I can check if a value is empty', () => {
    expect(createValue(description, denormalizeChannelReference(null), enUS, denormalizeTextData('')).isEmpty()).toBe(
      true
    );
    expect(createValue(description, denormalizeChannelReference(null), enUS, denormalizeTextData(null)).isEmpty()).toBe(
      true
    );
  });

  test('I can check if a value is empty', () => {
    expect(createValue(description, denormalizeChannelReference(null), enUS, denormalizeTextData('')).isEmpty()).toBe(
      true
    );
    expect(createValue(description, denormalizeChannelReference(null), enUS, denormalizeTextData(null)).isEmpty()).toBe(
      true
    );
  });

  test('I can check equality on values', () => {
    expect(
      createValue(description, denormalizeChannelReference(null), enUS, denormalizeTextData('')).equals(
        createValue(description, denormalizeChannelReference(null), enUS, denormalizeTextData(null))
      )
    ).toBe(true);
    expect(
      createValue(description, denormalizeChannelReference(null), enUS, denormalizeTextData('')).equals(
        createValue(
          description,
          denormalizeChannelReference(null),
          denormalizeLocaleReference('fr_FR'),
          denormalizeTextData(null)
        )
      )
    ).toBe(false);
  });

  test('I can check if a value is complete', () => {
    expect(
      createValue(
        description,
        denormalizeChannelReference(null),
        enUS,
        denormalizeTextData('test description')
      ).isComplete()
    ).toBe(true);
    expect(
      createValue(description, denormalizeChannelReference(null), enUS, denormalizeTextData('')).isComplete()
    ).toBe(false);
  });

  test('I can check if a value is required', () => {
    expect(
      createValue(description, denormalizeChannelReference(null), enUS, denormalizeTextData('')).isRequired()
    ).toBe(true);
    expect(createValue(website, denormalizeChannelReference(null), enUS, denormalizeTextData('')).isRequired()).toBe(
      false
    );
  });

  test("I can normalize a value to it's minimal value", () => {
    expect(createValue(description, denormalizeChannelReference(null), enUS, data).normalizeMinimal()).toEqual({
      attribute: 'description_1234',
      channel: null,
      data: 'a nice description',
      locale: 'en_US',
    });
    expect(
      createValue(description, denormalizeChannelReference(null), enUS, denormalizeTextData(null)).normalizeMinimal()
    ).toEqual({
      attribute: 'description_1234',
      channel: null,
      data: null,
      locale: 'en_US',
    });
  });
});
