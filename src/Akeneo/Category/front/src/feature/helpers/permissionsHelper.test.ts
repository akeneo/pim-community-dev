import {alterPermissionsConsistently, ensureSubset, ensureSuperset} from './permissionsHelper';
import {UserGroup} from '../hooks/useFetchUserGroups';
import {CategoryPermission} from '../models/CategoryPermission';

const sortPermission = (permission1: CategoryPermission, permission2: CategoryPermission): number =>
  permission1.id - permission2.id;
describe('permissionsHelper', () => {
  test.each`
    a1                                                              | a2                                                              | expected
    ${[]}                                                           | ${[]}                                                           | ${[]}
    ${[]}                                                           | ${[]}                                                           | ${[]}
    ${[{id: 1, label: ''}]}                                         | ${[]}                                                           | ${[]}
    ${[{id: 1, label: ''}, {id: 2, label: ''}]}                     | ${[]}                                                           | ${[]}
    ${[{id: 1, label: ''}]}                                         | ${[{id: 1, label: ''}]}                                         | ${[{id: 1, label: ''}]}
    ${[{id: 1, label: ''}]}                                         | ${[{id: 1, label: ''}, {id: 2, label: ''}]}                     | ${[{id: 1, label: ''}]}
    ${[{id: 1, label: ''}, {id: 2, label: ''}]}                     | ${[{id: 1, label: ''}]}                                         | ${[{id: 1, label: ''}]}
    ${[{id: 2, label: ''}]}                                         | ${[{id: 1, label: ''}]}                                         | ${[]}
    ${[{id: 2, label: ''}, {id: 3, label: ''}]}                     | ${[{id: 1, label: ''}, {id: 2, label: ''}]}                     | ${[{id: 2, label: ''}]}
    ${[{id: 2, label: ''}, {id: 3, label: ''}, {id: 1, label: ''}]} | ${[{id: 7, label: ''}, {id: 3, label: ''}, {id: 2, label: ''}]} | ${[{id: 2, label: ''}, {id: 3, label: ''}]}
    ${[{id: 3, label: ''}, {id: 4, label: ''}]}                     | ${[{id: 1, label: ''}, {id: 2, label: ''}]}                     | ${[]}
  `('the biggest subset of $a2 build from $a1 is $expected', ({a1, a2, expected}) => {
    const sortedResult = ensureSubset(a1, a2).sort(sortPermission);
    const sortedExpected = expected.sort(sortPermission);
    expect(sortedResult).toEqual(sortedExpected);
  });

  test.each`
    a1                                          | a2                                          | expected
    ${[]}                                       | ${[]}                                       | ${[]}
    ${[{id: 1, label: ''}]}                     | ${[]}                                       | ${[{id: 1, label: ''}]}
    ${[]}                                       | ${[{id: 1, label: ''}]}                     | ${[{id: 1, label: ''}]}
    ${[{id: 1, label: ''}]}                     | ${[{id: 2, label: ''}]}                     | ${[{id: 1, label: ''}, {id: 2, label: ''}]}
    ${[{id: 1, label: ''}, {id: 2, label: ''}]} | ${[{id: 2, label: ''}]}                     | ${[{id: 1, label: ''}, {id: 2, label: ''}]}
    ${[{id: 1, label: ''}, {id: 2, label: ''}]} | ${[{id: 1, label: ''}, {id: 2, label: ''}]} | ${[{id: 1, label: ''}, {id: 2, label: ''}]}
    ${[{id: 1, label: ''}, {id: 2, label: ''}]} | ${[{id: 2, label: ''}, {id: 3, label: ''}]} | ${[{id: 3, label: ''}, {id: 2, label: ''}, {id: 1, label: ''}]}
    ${[{id: 1, label: ''}, {id: 2, label: ''}]} | ${[{id: 3, label: ''}, {id: 4, label: ''}]} | ${[{id: 1, label: ''}, {id: 2, label: ''}, {id: 3, label: ''}, {id: 4, label: ''}]}
  `('the smallest superset of $a2 build from $a1 is $expected', ({a1, a2, expected}) => {
    const sortedResult = ensureSuperset(a1, a2).sort(sortPermission);
    const sortedExpected = expected.sort(sortPermission);
    expect(sortedResult).toEqual(sortedExpected);
  });

  test.each`
    permissions                                                                                                                                                                                           | changes                           | expectedConsistentPermissions
    ${{view: [], edit: [], own: []}}                                                                                                                                                                      | ${{type: 'view', values: []}}     | ${{view: [], edit: [], own: []}}
    ${{view: [], edit: [], own: []}}                                                                                                                                                                      | ${{type: 'view', values: [1]}}    | ${{view: [{id: 1, label: 'IT support'}], edit: [], own: []}}
    ${{view: [], edit: [], own: []}}                                                                                                                                                                      | ${{type: 'edit', values: [1]}}    | ${{view: [{id: 1, label: 'IT support'}], edit: [{id: 1, label: 'IT support'}], own: []}}
    ${{view: [], edit: [], own: []}}                                                                                                                                                                      | ${{type: 'own', values: [1]}}     | ${{view: [{id: 1, label: 'IT support'}], edit: [{id: 1, label: 'IT support'}], own: [{id: 1, label: 'IT support'}]}}
    ${{view: [{id: 1, label: 'IT support'}], edit: [{id: 2, label: 'Manager'}], own: [{id: 2, label: 'Manager'}]}}                                                                                        | ${{type: 'view', values: [2]}}    | ${{view: [{id: 2, label: 'Manager'}], edit: [{id: 2, label: 'Manager'}], own: [{id: 2, label: 'Manager'}]}}
    ${{view: [{id: 1, label: 'IT support'}], edit: [{id: 2, label: 'Manager'}], own: [{id: 2, label: 'Manager'}]}}                                                                                        | ${{type: 'view', values: [1, 2]}} | ${{view: [{id: 2, label: 'Manager'}, {id: 1, label: 'IT support'}], edit: [{id: 2, label: 'Manager'}], own: [{id: 2, label: 'Manager'}]}}
    ${{view: [{id: 1, label: 'IT support'}], edit: [{id: 2, label: 'Manager'}], own: [{id: 2, label: 'Manager'}]}}                                                                                        | ${{type: 'view', values: [1]}}    | ${{view: [{id: 1, label: 'IT support'}], edit: [], own: []}}
    ${{view: [{id: 1, label: 'IT support'}, {id: 2, label: 'Manager'}], edit: [{id: 1, label: 'IT support'}], own: []}}                                                                                   | ${{type: 'view', values: [1, 3]}} | ${{view: [{id: 1, label: 'IT support'}, {id: 3, label: 'Furniture manager'}], edit: [{id: 1, label: 'IT support'}], own: []}}
    ${{view: [{id: 1, label: 'IT support'}, {id: 2, label: 'Manager'}], edit: [{id: 1, label: 'IT support'}], own: []}}                                                                                   | ${{type: 'edit', values: [3]}}    | ${{view: [{id: 1, label: 'IT support'}, {id: 2, label: 'Manager'}, {id: 3, label: 'Furniture manager'}], edit: [{id: 3, label: 'Furniture manager'}], own: []}}
    ${{view: [{id: 1, label: 'IT support'}, {id: 2, label: 'Manager'}], edit: [{id: 1, label: 'IT support'}, {id: 2, label: 'Manager'}], own: [{id: 1, label: 'IT support'}, {id: 2, label: 'Manager'}]}} | ${{type: 'edit', values: [1, 3]}} | ${{view: [{id: 1, label: 'IT support'}, {id: 2, label: 'Manager'}, {id: 3, label: 'Furniture manager'}], edit: [{id: 1, label: 'IT support'}, {id: 3, label: 'Furniture manager'}], own: [{id: 1, label: 'IT support'}]}}
    ${{view: [{id: 1, label: 'IT support'}], edit: [{id: 1, label: 'IT support'}], own: [{id: 1, label: 'IT support'}]}}                                                                                  | ${{type: 'own', values: [1, 2]}}  | ${{view: [{id: 1, label: 'IT support'}, {id: 2, label: 'Manager'}], edit: [{id: 1, label: 'IT support'}, {id: 2, label: 'Manager'}], own: [{id: 1, label: 'IT support'}, {id: 2, label: 'Manager'}]}}
    ${{view: [{id: 1, label: 'IT support'}], edit: [{id: 1, label: 'IT support'}, {id: 2, label: 'Manager'}], own: [{id: 1, label: 'IT support'}]}}                                                       | ${{type: 'own', values: [3]}}     | ${{view: [{id: 1, label: 'IT support'}, {id: 2, label: 'Manager'}, {id: 3, label: 'Furniture manager'}], edit: [{id: 1, label: 'IT support'}, {id: 2, label: 'Manager'}, {id: 3, label: 'Furniture manager'}], own: [{id: 3, label: 'Furniture manager'}]}}
  `(
    'consistent permissions build from {view:$permissions.view,edit:$permissions.edit,own:$permissions.own} by applying changes {type:$changes.type, values:$changes.values} is : {view:$expectedConsistentPermissions.view,edit:$expectedConsistentPermissions.edit,own:$expectedConsistentPermissions.own}',
    ({permissions, changes, expectedConsistentPermissions}) => {
      const userGroups: UserGroup[] = [
        {
          id: '1',
          label: 'IT support',
          isDefault: false,
        },
        {
          id: '2',
          label: 'Manager',
          isDefault: false,
        },
        {
          id: '3',
          label: 'Furniture manager',
          isDefault: false,
        },
        {
          id: '4',
          label: 'Clothes manager',
          isDefault: false,
        },
      ];
      const result = alterPermissionsConsistently(userGroups, permissions, changes);
      result.view.sort(sortPermission);
      result.edit.sort(sortPermission);
      result.own.sort(sortPermission);
      expectedConsistentPermissions.view.sort(sortPermission);
      expectedConsistentPermissions.edit.sort(sortPermission);
      expectedConsistentPermissions.own.sort(sortPermission);

      expect(result.view).toEqual(expectedConsistentPermissions.view);
      expect(result.edit).toEqual(expectedConsistentPermissions.edit);
      expect(result.own).toEqual(expectedConsistentPermissions.own);
    }
  );
});
