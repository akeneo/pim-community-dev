import {
  setValueData,
  isValueEmpty,
  isValueComplete,
  isValueRequired,
  areValuesEqual,
  normalizeValue,
  getEditionValueMediaPreview,
} from 'akeneoassetmanager/domain/model/asset/edition-value';
import {MediaPreviewType} from 'akeneoassetmanager/tools/media-url-generator';
import {MEDIA_FILE_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {MEDIA_LINK_ATTRIBUTE_TYPE} from 'akeneoassetmanager/domain/model/attribute/type/media-link';

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
const mediaFileAttribute = {
  identifier: 'front_view',
  asset_family_identifier: 'designer',
  code: 'front_view',
  labels: {en_US: 'Front view'},
  type: MEDIA_FILE_ATTRIBUTE_TYPE,
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  max_file_size: null,
  allowed_extensions: [],
};
const mediaLinkAttribute = {
  identifier: 'url',
  asset_family_identifier: 'designer',
  code: 'url',
  labels: {en_US: 'Url'},
  type: MEDIA_LINK_ATTRIBUTE_TYPE,
  order: 0,
  value_per_locale: true,
  value_per_channel: false,
  is_required: true,
  suffix: '.jpg',
  prefix: 'https://',
  media_type: 'image',
};
const descriptionAttribute = normalizedDescription;
const enUS = 'en_US';
const niceDescription = 'nice description';
const newDescription = 'new description';
const descriptionEditionValue = {
  attribute: descriptionAttribute,
  channel: null,
  locale: enUS,
  data: niceDescription,
};
const unscopedEditionValue = {
  attribute: descriptionAttribute,
  channel: null,
  locale: null,
  data: niceDescription,
};
const nullEditionValue = {
  ...descriptionEditionValue,
  data: null,
};
const mediaFileEditionValue = {
  ...descriptionEditionValue,
  attribute: mediaFileAttribute,
  data: {
    originalFilename: 'image',
    filePath: 'image.jpg',
  },
};
const mediaLinkEditionValue = {
  ...descriptionEditionValue,
  attribute: mediaLinkAttribute,
  data: 'imagePath',
};

describe('akeneo > asset family > domain > model > asset --- edition-value', () => {
  test('I can set data to an EditionValue', () => {
    expect(setValueData(descriptionEditionValue, newDescription)).toEqual({
      ...descriptionEditionValue,
      data: newDescription,
    });
  });

  test('I can test if an EditionValue is empty', () => {
    expect(isValueEmpty(descriptionEditionValue)).toBe(false);
    expect(isValueEmpty(nullEditionValue)).toBe(true);
  });

  test('I can test if an EditionValue is complete', () => {
    expect(isValueComplete(descriptionEditionValue)).toBe(true);
    expect(isValueComplete(nullEditionValue)).toBe(false);
  });

  test('I can test if an EditionValue is required', () => {
    expect(isValueRequired(descriptionEditionValue)).toBe(true);
    expect(isValueRequired(nullEditionValue)).toBe(true);
    // TODO with non required value
  });

  test('I can test if two EditionValue are equal', () => {
    expect(areValuesEqual(descriptionEditionValue, descriptionEditionValue)).toBe(true);
    expect(areValuesEqual(descriptionEditionValue, unscopedEditionValue)).toBe(false);
  });

  test('I can normalize an EditionValue', () => {
    expect(normalizeValue(descriptionEditionValue)).toEqual(descriptionEditionValue);
  });

  test('I can get the EditionValue preview url', () => {
    expect(
      getEditionValueMediaPreview(MediaPreviewType.Thumbnail, mediaFileEditionValue, mediaFileAttribute.identifier)
    ).toEqual({
      type: MediaPreviewType.Thumbnail,
      attributeIdentifier: mediaFileAttribute.identifier,
      data: 'image.jpg',
    });
    expect(
      getEditionValueMediaPreview(MediaPreviewType.Thumbnail, mediaLinkEditionValue, mediaLinkAttribute.identifier)
    ).toEqual({
      type: MediaPreviewType.Thumbnail,
      attributeIdentifier: mediaLinkAttribute.identifier,
      data: 'imagePath',
    });
  });
});
