import {denormalize as denormalizeTextAttribute} from 'akeneoassetmanager/domain/model/attribute/type/text';
import {
  setValueData,
  isValueEmpty,
  isValueComplete,
  areValuesEqual,
  normalizeValue,
} from 'akeneoassetmanager/domain/model/asset/value';

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
const normalizedWebsite = {
  identifier: 'website_1234',
  asset_family_identifier: 'designer',
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

const value = {
  attribute: normalizedDescription,
  channel: null,
  locale: null,
  data: 'toto',
};

describe('akeneo > asset family > domain > model > asset --- value', () => {
  test('it can set a Data to a Value', () => {
    expect(setValueData(value, 'titi')).toEqual({
      attribute: normalizedDescription,
      channel: null,
      locale: null,
      data: 'titi',
    });
  });

  test('it can tell if a Value is empty', () => {
    expect(isValueEmpty(value)).toEqual(false);
    expect(isValueEmpty({...value, data: null})).toEqual(true);
  });

  test('it can tell if a Value is complete', () => {
    const valueComplete = value;
    const valueIncomplete = {...value, data: null};
    expect(isValueComplete(valueComplete)).toEqual(true);
    expect(isValueComplete(valueIncomplete)).toEqual(false);
  });

  test('it can tell if a Value is required', () => {
    const valueRequired = value;
    const valueNotRequired = {...value, attribute: normalizedWebsite};
    expect(isValueComplete(valueRequired)).toEqual(true);
    expect(isValueComplete(valueNotRequired)).toEqual(false);
  });

  test('it can tell if a Value is equal to another', () => {
    const firstValue = value;
    const secondValue = {...value, channel: 'en_US'};
    expect(areValuesEqual(firstValue, firstValue)).toEqual(true);
    expect(areValuesEqual(firstValue, secondValue)).toEqual(false);
  });

  test('it can normalize a Value', () => {
    expect(normalizeValue(value)).toEqual(value);
  });
});
