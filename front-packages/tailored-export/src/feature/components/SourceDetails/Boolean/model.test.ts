import {isBooleanSource} from './model';

test('it validates that its a boolean source', () => {
  expect(
    isBooleanSource({
      uuid: '123',
      code: 'a code',
      type: 'attribute',
      locale: 'fr_FR',
      channel: 'ecommerce',
      operations: {},
      selection: {type: 'code'},
    })
  ).toEqual(true);

  expect(
    isBooleanSource({
      uuid: '123',
      code: 'a code',
      type: 'attribute',
      locale: 'fr_FR',
      channel: 'ecommerce',
      operations: {
        replacement: {
          type: 'replacement',
          mapping: {
            true: 'yes',
            false: 'no',
          },
        },
      },
      selection: {type: 'code'},
    })
  ).toEqual(true);

  expect(
    isBooleanSource({
      uuid: '123',
      code: 'a code',
      type: 'attribute',
      locale: 'fr_FR',
      channel: 'ecommerce',
      operations: {
        default_value: {
          type: 'default_value',
          value: 'a default value',
        },
      },
      selection: {type: 'code'},
    })
  ).toEqual(true);

  expect(
    isBooleanSource({
      uuid: '123',
      code: 'a code',
      type: 'attribute',
      locale: 'fr_FR',
      channel: 'ecommerce',
      operations: {
        foo: 'bar',
      },
      selection: {type: 'code'},
    })
  ).toEqual(false);
});
