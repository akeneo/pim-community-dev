import {
  setValueData,
  isValueEmpty,
  areValuesEqual,
  normalizeValue,
  getListValueMediaPreview,
} from 'akeneoassetmanager/domain/model/asset/list-value';
import {MediaPreviewType} from 'akeneoassetmanager/tools/media-url-generator';

const enUS = 'en_US';
const niceDescription = 'nice description';
const newDescription = 'new description';
const attributeIdentifier = 'description_fingerprint';
const descriptionListValue = {
  attribute: attributeIdentifier,
  channel: null,
  locale: enUS,
  data: niceDescription,
};
const unscopedListValue = {
  attribute: attributeIdentifier,
  channel: null,
  locale: null,
  data: niceDescription,
};
const nullEditionValue = {
  ...descriptionListValue,
  data: null,
};
const imageListValue = {
  ...descriptionListValue,
  data: 'image.jpg',
};

describe('akeneo > asset family > domain > model > asset --- list-value', () => {
  test('I can set data to an EditionValue', () => {
    expect(setValueData(descriptionListValue, newDescription)).toEqual({
      ...descriptionListValue,
      data: newDescription,
    });
  });

  test('I can test if an EditionValue is empty', () => {
    expect(isValueEmpty(descriptionListValue)).toBe(false);
    expect(isValueEmpty(nullEditionValue)).toBe(true);
  });

  test('I can test if two EditionValue are equal', () => {
    expect(areValuesEqual(descriptionListValue, descriptionListValue)).toBe(true);
    expect(areValuesEqual(descriptionListValue, unscopedListValue)).toBe(false);
  });

  test('I can normalize an EditionValue', () => {
    expect(normalizeValue(descriptionListValue)).toEqual(descriptionListValue);
  });

  test('I can get the ListValue preview url', () => {
    expect(getListValueMediaPreview(MediaPreviewType.Thumbnail, imageListValue, attributeIdentifier)).toEqual({
      type: MediaPreviewType.Thumbnail,
      attributeIdentifier,
      data: 'image.jpg',
    });
  });
});
