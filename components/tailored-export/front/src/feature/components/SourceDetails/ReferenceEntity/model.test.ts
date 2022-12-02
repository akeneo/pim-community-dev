import {isDefaultReferenceEntitySelection, isReferenceEntitySource, ReferenceEntitySource} from './model';

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
