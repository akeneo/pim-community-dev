import {removeDefaultUserGroup} from './Automation';

test('it remove the default user group', async () => {
  expect(removeDefaultUserGroup([])).toEqual([]);
  expect(removeDefaultUserGroup([{id: 1, label: 'All'}])).toEqual([]);
  expect(
    removeDefaultUserGroup([
      {id: 1, label: 'All'},
      {id: 2, label: 'Support IT'},
    ])
  ).toEqual([{id: 2, label: 'Support IT'}]);
  expect(
    removeDefaultUserGroup([
      {id: 3, label: 'Manager'},
      {id: 2, label: 'Support IT'},
    ])
  ).toEqual([
    {id: 3, label: 'Manager'},
    {id: 2, label: 'Support IT'},
  ]);
});
