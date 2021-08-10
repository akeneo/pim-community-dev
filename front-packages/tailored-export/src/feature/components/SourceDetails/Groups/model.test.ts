import {GroupsSource, isGroupsSource} from "./model";

const source: GroupsSource = {
  uuid: '123',
  code: 'groups',
  type: 'property',
  locale: null,
  channel: null,
  operations: {},
  selection: {type: 'code', separator: ','},
};

test('it validates that something is a group source', () => {
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
    // @ts-expect-error invalid operations
    isGroupsSource({
      ...source,
      operations: {
        foo: 'bar',
      },
    })
  ).toEqual(false);
});
