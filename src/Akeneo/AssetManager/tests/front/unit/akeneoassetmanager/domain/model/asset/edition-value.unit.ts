import {
  isValueComplete,
  isValueRequired,
  areValuesEqual,
  isEditionValue,
} from 'akeneoassetmanager/domain/model/asset/edition-value';
import {setValueData, isValueEmpty, getPreviewModelFromValue} from 'akeneoassetmanager/domain/model/asset/value';

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

  test('I can test if something is an EditionValue', () => {
    expect(isEditionValue(descriptionEditionValue)).toBe(true);
    expect(isEditionValue({some: 'thing'})).toBe(false);
  });

  test('I can get the PreviewModel of an EditionValue', () => {
    const previewModel = getPreviewModelFromValue(descriptionEditionValue, null, enUS);

    expect(previewModel).toEqual({
      data: niceDescription,
      channel: null,
      locale: enUS,
      attribute: 'description_1234',
    });
  });
});
