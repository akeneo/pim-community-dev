import {areValuesEqual} from 'akeneoassetmanager/domain/model/asset/list-value';

const enUS = 'en_US';
const niceDescription = 'nice description';
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

describe('akeneo > asset family > domain > model > asset --- list-value', () => {
  test('I can test if two EditionValue are equal', () => {
    expect(areValuesEqual(descriptionListValue, descriptionListValue)).toBe(true);
    expect(areValuesEqual(descriptionListValue, unscopedListValue)).toBe(false);
  });
});
