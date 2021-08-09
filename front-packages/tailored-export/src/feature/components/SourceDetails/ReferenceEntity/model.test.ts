import {isReferenceEntitySource, ReferenceEntitySource} from './model';

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
        foo: 'bar',
      },
    })
  ).toEqual(false);
});
