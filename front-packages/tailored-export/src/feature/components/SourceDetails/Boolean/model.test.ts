import {Source} from '../../../models';
import {isBooleanSource} from './model';

const source: Source = {
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

  expect(
    isBooleanSource({
      ...source,
      operations: {
        foo: 'bar',
      },
    })
  ).toEqual(false);
});
