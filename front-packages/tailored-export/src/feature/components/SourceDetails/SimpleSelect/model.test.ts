import {Source} from '../../../models';
import {isSimpleSelectSource} from './model';

const source: Source = {
  uuid: '123',
  code: 'a code',
  type: 'attribute',
  locale: 'fr_FR',
  channel: 'ecommerce',
  operations: {},
  selection: {type: 'code'},
};

test('it validates that something is a simple select source', () => {
  expect(isSimpleSelectSource(source)).toEqual(true);

  expect(
    isSimpleSelectSource({
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
    isSimpleSelectSource({
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
    isSimpleSelectSource({
      ...source,
      operations: {
        foo: 'bar',
      },
    })
  ).toEqual(false);
});
