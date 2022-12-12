import {ReferenceEntityAttribute} from 'feature/models';
import {
  getDefaultReferenceEntityAttributeSelection,
  isDefaultReferenceEntitySelection,
  isReferenceEntitySource,
  ReferenceEntitySource,
} from './model';

const source: ReferenceEntitySource = {
  uuid: '123',
  code: 'a code',
  type: 'attribute',
  locale: 'fr_FR',
  channel: 'ecommerce',
  operations: {},
  selection: {type: 'code'},
};

test('it validates that something is a reference entity source', () => {
  expect(isReferenceEntitySource(source)).toEqual(true);

  expect(
    isReferenceEntitySource({
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
    isReferenceEntitySource({
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
    isReferenceEntitySource({
      ...source,
      operations: {
        foo: 'bar',
      },
    })
  ).toEqual(false);
});

test('it can test that a selection is the default Reference Entity selection', () => {
  expect(isDefaultReferenceEntitySelection({type: 'code'})).toEqual(true);
  expect(
    isDefaultReferenceEntitySelection({
      type: 'attribute',
      attribute_identifier: 'color_1234',
      attribute_type: 'text',
      reference_entity_code: 'designer',
      channel: null,
      locale: null,
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

  expect(getDefaultReferenceEntityAttributeSelection(attribute, 'designer', null, null)).toEqual({
    type: 'attribute',
    attribute_identifier: 'name_1234',
    attribute_type: 'text',
    reference_entity_code: 'designer',
    channel: null,
    locale: null,
  });
  expect(
    getDefaultReferenceEntityAttributeSelection({...attribute, type: 'number'}, 'designer', 'ecommerce', 'en_US')
  ).toEqual({
    type: 'attribute',
    attribute_identifier: 'name_1234',
    attribute_type: 'number',
    reference_entity_code: 'designer',
    channel: 'ecommerce',
    locale: 'en_US',
    decimal_separator: '.',
  });
  expect(
    getDefaultReferenceEntityAttributeSelection({...attribute, type: 'option'}, 'designer', null, 'en_US')
  ).toEqual({
    type: 'attribute',
    attribute_identifier: 'name_1234',
    attribute_type: 'option',
    reference_entity_code: 'designer',
    channel: null,
    locale: 'en_US',
    option_selection: {type: 'code'},
  });
  expect(
    getDefaultReferenceEntityAttributeSelection(
      {...attribute, type: 'option_collection'},
      'designer',
      'ecommerce',
      null
    )
  ).toEqual({
    type: 'attribute',
    attribute_identifier: 'name_1234',
    attribute_type: 'option_collection',
    reference_entity_code: 'designer',
    channel: 'ecommerce',
    locale: null,
    option_selection: {type: 'code', separator: ','},
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

  expect(() => getDefaultReferenceEntityAttributeSelection(imageAttribute, 'designer', null, null)).toThrowError(
    'Unsupported attribute type "image"'
  );
});
