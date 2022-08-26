import {GroupsSource, isGroupsSource} from './model';

const source: GroupsSource = {
  uuid: '123',
  code: 'groups',
  type: 'property',
  locale: null,
  channel: null,
  operations: {},
  selection: {type: 'code', separator: ','},
};

test('it validates that something is a groups source', () => {
  expect(isGroupsSource(source)).toEqual(true);

  expect(
    isGroupsSource({
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
    isGroupsSource({
      ...source,
      operations: {
        // @ts-expect-error invalid operations
        foo: 'bar',
      },
    })
  ).toEqual(false);
});
