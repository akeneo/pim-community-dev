import {filterDefaultUserGroup} from './Automation';

test('it remove the default user group', async () => {
  expect(filterDefaultUserGroup([])).toEqual([]);
  expect(filterDefaultUserGroup(['All'])).toEqual(['All']);
  expect(filterDefaultUserGroup(['All', 'Support IT'])).toEqual(['Support IT']);
  expect(filterDefaultUserGroup(['Manager', 'Support IT'])).toEqual(['Manager', 'Support IT']);
});
