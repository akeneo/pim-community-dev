import {isFamilySource, FamilySource} from './model';

const source: FamilySource = {
  uuid: '123',
  code: 'family',
  type: 'property',
  locale: null,
  channel: null,
  operations: {},
  selection: {type: 'code'},
};

test('it validates that something is a family source', () => {
  expect(isFamilySource(source)).toEqual(true);

  expect(
    isFamilySource({
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
    isFamilySource({
      ...source,
      operations: {
        // @ts-expect-error invalid operations
        foo: 'bar',
      },
    })
  ).toEqual(false);
});
