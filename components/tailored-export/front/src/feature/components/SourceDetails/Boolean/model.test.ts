import {BooleanSource, isBooleanSource} from './model';

const source: BooleanSource = {
  uuid: '123',
  code: 'a code',
  type: 'attribute',
  locale: 'fr_FR',
  channel: 'ecommerce',
  operations: {},
  selection: {type: 'code'},
};

test('it validates that something is a boolean source', () => {
  expect(isBooleanSource(source)).toEqual(true);

  expect(
    isBooleanSource({
      ...source,
      operations: {
        replacement: {
          type: 'replacement',
          mapping: {
            true: 'yes',
            false: 'no',
          },
        },
      },
    })
  ).toEqual(true);

  expect(
    isBooleanSource({
      ...source,
      operations: {
        default_value: {
          type: 'default_value',
          value: 'a default value',
        },
      },
    })
  ).toEqual(true);

  const invalidSource: BooleanSource = {
    ...source,
    operations: {
      // @ts-expect-error invalid operations
      foo: 'bar',
    },
  };

  expect(isBooleanSource(invalidSource)).toEqual(false);
});
