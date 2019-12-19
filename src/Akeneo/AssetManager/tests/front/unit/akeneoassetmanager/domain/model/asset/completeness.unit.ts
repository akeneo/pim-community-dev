import Completeness from 'akeneoassetmanager/domain/model/asset/completeness';
import {denormalize as denormalizeTextAttribute} from 'akeneoassetmanager/domain/model/attribute/type/text';
import {createValueCollection} from 'akeneoassetmanager/domain/model/asset/value-collection';

const normalizedDescription = {
  identifier: 'description_1234',
  asset_family_identifier: 'designer',
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
const normalizedWebsite = {
  identifier: 'website_1234',
  asset_family_identifier: 'designer',
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

const normalizedNickname = {
  identifier: 'nickname_1234',
  asset_family_identifier: 'designer',
  code: 'nickname',
  labels: {en_US: 'Nickname'},
  type: 'text',
  order: 0,
  value_per_locale: true,
  value_per_channel: true,
  is_required: false,
  max_length: 0,
  is_textarea: false,
  is_rich_text_editor: false,
  validation_rule: 'url',
  regular_expression: null,
};
const descriptionData = 'a nice description';
const descriptionValue = {
  attribute: normalizedDescription,
  channel: 'ecommerce',
  locale: 'en_US',
  data: descriptionData,
};
const websiteData = null;
const websiteValue = {
  attribute: normalizedWebsite,
  channel: 'ecommerce',
  locale: 'en_US',
  data: websiteData,
};
const nicknameData = 'Pedro';
const nicknameValue = {
  attribute: normalizedNickname,
  channel: 'ecommerce',
  locale: 'en_US',
  data: nicknameData,
};
const valueCollection = createValueCollection([descriptionValue, websiteValue]);
const valueCollectionWithNoRequired = createValueCollection([nicknameValue]);
const valueCollectionIncomplete = createValueCollection([websiteValue]);
const valueCollectionComplete = createValueCollection([descriptionValue]);
const channelEcommerce = 'ecommerce';
const localeEnUs = 'en_US';

describe('akeneo > asset > domain > model --- completeness', () => {
  test('I can create from the normalized', () => {
    const completeness = Completeness.createFromNormalized({complete: 0, required: 0});
    expect(completeness.getCompleteAttributeCount()).toBe(0);
    expect(completeness.getRequiredAttributeCount()).toBe(0);
  });

  test('I can create the completeness from the values', () => {
    const completeness = Completeness.createFromValues(
      valueCollection.getValuesForChannelAndLocale(channelEcommerce, localeEnUs)
    );
    expect(completeness.getCompleteAttributeCount()).toBe(1);
    expect(completeness.getRequiredAttributeCount()).toBe(2);
  });

  test('I can get the ratio', () => {
    const completeness = Completeness.createFromValues(
      valueCollection.getValuesForChannelAndLocale(channelEcommerce, localeEnUs)
    );
    expect(completeness.getRatio()).toBe(50);
  });

  test('I can get the ratio if there is no required attributes', () => {
    const completeness = Completeness.createFromValues([]);
    expect(completeness.getRatio()).toBe(0);
  });

  test('I can know if there is no required attribute', () => {
    const completeness = Completeness.createFromValues(
      valueCollection.getValuesForChannelAndLocale(channelEcommerce, localeEnUs)
    );
    expect(!completeness.hasRequiredAttribute()).toBe(false);

    const completenessWithNoRequired = Completeness.createFromValues(
      valueCollectionWithNoRequired.getValuesForChannelAndLocale(channelEcommerce, localeEnUs)
    );
    expect(!completenessWithNoRequired.hasRequiredAttribute()).toBe(true);
  });

  test('I can know if there is no complete attribute', () => {
    const completeness = Completeness.createFromValues(
      valueCollection.getValuesForChannelAndLocale(channelEcommerce, localeEnUs)
    );
    expect(completeness.hasCompleteAttribute()).toBe(true);

    const completenessWithNoComplete = Completeness.createFromValues(
      valueCollectionIncomplete.getValuesForChannelAndLocale(channelEcommerce, localeEnUs)
    );
    expect(completenessWithNoComplete.hasCompleteAttribute()).toBe(false);
  });

  test('I can know if the completeness is complete', () => {
    const completeness = Completeness.createFromValues(
      valueCollectionComplete.getValuesForChannelAndLocale(channelEcommerce, localeEnUs)
    );
    expect(completeness.isComplete()).toBe(true);

    const completenessWithNoComplete = Completeness.createFromValues(
      valueCollectionIncomplete.getValuesForChannelAndLocale(channelEcommerce, localeEnUs)
    );
    expect(completenessWithNoComplete.isComplete()).toBe(false);
  });
});
