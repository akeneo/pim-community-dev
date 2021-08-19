import {Source} from '../../../models';
import {isDateSource} from './model';

const source: Source = {
  uuid: '123',
  code: 'a code',
  type: 'attribute',
  locale: 'fr_FR',
  channel: 'ecommerce',
  operations: {},
  selection: {format: 'yyyy-mm-dd'},
};

test('it validates that something is a date source', () => {
  expect(isDateSource(source)).toEqual(true);

  expect(
    isDateSource({
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
    isDateSource({
      ...source,
      operations: {
        foo: 'bar',
      },
    })
  ).toEqual(false);
});
