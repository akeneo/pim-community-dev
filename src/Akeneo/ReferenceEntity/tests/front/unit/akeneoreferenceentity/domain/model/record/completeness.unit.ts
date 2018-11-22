import Completeness from 'akeneoreferenceentity/domain/model/record/completeness';
import {denormalize as denormalizeTextAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/text';
import {denormalizeChannelReference} from 'akeneoreferenceentity/domain/model/channel-reference';
import {denormalizeLocaleReference} from 'akeneoreferenceentity/domain/model/locale-reference';
import {denormalize as denormalizeTextData} from 'akeneoreferenceentity/domain/model/record/data/text';
import {createValue} from 'akeneoreferenceentity/domain/model/record/value';
import {createValueCollection} from 'akeneoreferenceentity/domain/model/record/value-collection';
import {createChannelReference} from 'akeneoreferenceentity/domain/model/channel-reference';
import {createLocaleReference} from 'akeneoreferenceentity/domain/model/locale-reference';

const normalizedDescription = {
  identifier: 'description_1234',
  reference_entity_identifier: 'designer',
  code: 'description',
  labels: {en_US: 'Description'},
  type: 'text',
  order: 0,
  value_per_locale: true,
  value_per_channel: true,
  is_required: true,
  max_length: 0,
  is_textarea: false,
  is_rich_text_editor: false,
  validation_rule: 'email',
  regular_expression: null,
};
const description = denormalizeTextAttribute(normalizedDescription);
const normalizedWebsite = {
  identifier: 'website_1234',
  reference_entity_identifier: 'designer',
  code: 'website',
  labels: {en_US: 'Website'},
  type: 'text',
  order: 0,
  value_per_locale: true,
  value_per_channel: true,
  is_required: true,
  max_length: 0,
  is_textarea: false,
  is_rich_text_editor: false,
  validation_rule: 'url',
  regular_expression: null,
};
const website = denormalizeTextAttribute(normalizedWebsite);
const descriptionData = denormalizeTextData('a nice description');
const descriptionValue = createValue(description, denormalizeChannelReference('ecommerce'), denormalizeLocaleReference('en_US'), descriptionData);
const websiteData = denormalizeTextData('');
const websiteValue = createValue(website, denormalizeChannelReference('ecommerce'), denormalizeLocaleReference('en_US'), websiteData);
const valueCollection = createValueCollection([descriptionValue, websiteValue]);
const channelEcommerce = createChannelReference('ecommerce');
const localeFr = createLocaleReference('en_US');

describe('akeneo > record > domain > model --- completeness', () => {
  test('I can create from the normalized', () => {
    const completeness = Completeness.createFromNormalized({complete: 0, required: 0})
    expect(completeness.getComplete()).toBe(0);
    expect(completeness.getRequired()).toBe(0);
  });

  test('I can create the completeness from the values', () => {
    const completeness = Completeness.createFromValues(valueCollection.getValuesForChannelAndLocale(channelEcommerce, localeFr));
    expect(completeness.getComplete()).toBe(1);
    expect(completeness.getRequired()).toBe(2);
  });

  test('I can get the ratio', () => {
    const completeness = Completeness.createFromValues(valueCollection.getValuesForChannelAndLocale(channelEcommerce, localeFr));
    expect(completeness.getRatio()).toBe(50);
  });
});
