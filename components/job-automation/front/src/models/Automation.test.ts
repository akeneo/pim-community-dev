import {filterDefaultUserGroup} from './Automation';

test('it remove the default user group', async () => {
  expect(filterDefaultUserGroup([])).toEqual([]);
  expect(filterDefaultUserGroup([{id: 1, label: 'All'}])).toEqual([]);
  expect(filterDefaultUserGroup([{id: 1, label: 'All'}, {id: 2, label: 'Support IT'}])).toEqual(['Support IT']);
  expect(filterDefaultUserGroup([{id: 3, label: 'Manager'}, {id: 2, label: 'Support IT'}])).toEqual(['Manager', 'Support IT']);
});
