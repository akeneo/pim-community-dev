import {ReferenceEntityAttribute} from 'feature/models';
import {
  getDefaultReferenceEntityCollectionAttributeSelection,
  isReferenceEntityCollectionSource,
  ReferenceEntityCollectionSource,
} from './model';

const source: ReferenceEntityCollectionSource = {
  uuid: '123',
  code: 'a code',
  type: 'attribute',
  locale: 'fr_FR',
  channel: 'ecommerce',
  operations: {},
  selection: {type: 'code', separator: ';'},
};

test('it validates that something is a reference entity collection source', () => {
  expect(isReferenceEntityCollectionSource(source)).toEqual(true);

  expect(
    isReferenceEntityCollectionSource({
      ...source,
      operations: {
        default_value: {
          type: 'default_value',
          value: 'a default value',
        },
      },
    })
  ).toEqual(true);

  expect(
    isReferenceEntityCollectionSource({
      ...source,
      operations: {
        replacement: {
          type: 'replacement',
          mapping: {
            black: 'rouge',
            red: 'noir',
          },
        },
      },
    })
  ).toEqual(true);

  expect(
    // @ts-expect-error invalid operation
    isReferenceEntityCollectionSource({
      ...source,
      operations: {
        foo: 'bar',
      },
    })
  ).toEqual(false);
});

test('it can get the default attribute selection based on the attribute type', () => {
  const attribute: ReferenceEntityAttribute = {
    code: 'name',
    identifier: 'name_1234',
    type: 'text',
    labels: {},
    value_per_channel: false,
    value_per_locale: false,
  };

  expect(getDefaultReferenceEntityCollectionAttributeSelection(attribute, 'designer', null, null)).toEqual({
    type: 'attribute',
    separator: ',',
    attribute_identifier: 'name_1234',
    attribute_type: 'text',
    reference_entity_code: 'designer',
    channel: null,
    locale: null,
  });
  expect(
    getDefaultReferenceEntityCollectionAttributeSelection(
      {...attribute, type: 'number'},
      'designer',
      'ecommerce',
      'en_US'
    )
  ).toEqual({
    type: 'attribute',
    separator: ',',
    attribute_identifier: 'name_1234',
    attribute_type: 'number',
    reference_entity_code: 'designer',
    channel: 'ecommerce',
    locale: 'en_US',
    decimal_separator: '.',
  });
  expect(
    getDefaultReferenceEntityCollectionAttributeSelection(
      {...attribute, type: 'option'},
      'designer',
      'ecommerce',
      'en_US'
    )
  ).toEqual({
    type: 'attribute',
    separator: ',',
    attribute_identifier: 'name_1234',
    attribute_type: 'option',
    reference_entity_code: 'designer',
    channel: 'ecommerce',
    locale: 'en_US',
    option_selection: {type: 'code'},
  });
});

test('it throws when the attribute type is unsupported', () => {
  const imageAttribute: ReferenceEntityAttribute = {
    code: 'name',
    identifier: 'name_1234',
    type: 'image',
    labels: {},
    value_per_channel: false,
    value_per_locale: false,
  };

  expect(() =>
    getDefaultReferenceEntityCollectionAttributeSelection(imageAttribute, 'designer', null, null)
  ).toThrowError('Unsupported attribute type "image"');
});
