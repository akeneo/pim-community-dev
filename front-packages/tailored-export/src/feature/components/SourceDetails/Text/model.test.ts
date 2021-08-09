import {isTextSource} from './model';

test('it validates a text source', () => {
  expect(
    isTextSource({
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
    isTextSource({
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
    isTextSource({
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
