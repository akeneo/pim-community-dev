import {isNumberSource, NumberSource} from './model';

const source: NumberSource = {
  uuid: '123',
  code: 'a code',
  type: 'attribute',
  locale: 'fr_FR',
  channel: 'ecommerce',
  operations: {},
  selection: {decimal_separator: '.'},
};

test('it validates that something is a number source', () => {
  expect(isNumberSource(source)).toEqual(true);

  expect(
    isNumberSource({
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
    // @ts-expect-error invalid operations
    isNumberSource({
      ...source,
      operations: {
        foo: 'bar',
      },
    })
  ).toEqual(false);
});
