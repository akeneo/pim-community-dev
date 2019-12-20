import {
  setValueData,
  isValueEmpty,
  isValueComplete,
  isValueRequired,
  areValuesEqual,
  normalizeValue,
} from 'akeneoassetmanager/domain/model/asset/list-value';

const enUS = 'en_US';
const niceDescription = 'nice description';
const newDescription = 'new description';
const descriptionEditionValue = {
  attribute: 'description_fingerprint',
  channel: null,
  locale: enUS,
  data: niceDescription,
};
const unscopedEditionValue = {
  attribute: 'description_fingerprint',
  channel: null,
  locale: null,
  data: niceDescription,
};
const nullEditionValue = {
  ...descriptionEditionValue,
  data: null,
};

describe('akeneo > asset family > domain > model > asset --- list-value', () => {
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

  test('I can test if two EditionValue are equal', () => {
    expect(areValuesEqual(descriptionEditionValue, descriptionEditionValue)).toBe(true);
    expect(areValuesEqual(descriptionEditionValue, unscopedEditionValue)).toBe(false);
  });

  test('I can normalize an EditionValue', () => {
    expect(normalizeValue(descriptionEditionValue)).toEqual(descriptionEditionValue);
  });
});
