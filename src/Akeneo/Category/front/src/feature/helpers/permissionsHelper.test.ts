import {alterPermissionsConsistently, ensureSubset, ensureSuperset} from './permissionsHelper';

describe('permissionsHelper', () => {
  test.each`
    a1           | a2           | expected
    ${[]}        | ${[]}        | ${[]}
    ${[1]}       | ${[]}        | ${[]}
    ${[1, 2]}    | ${[]}        | ${[]}
    ${[1]}       | ${[1]}       | ${[1]}
    ${[1]}       | ${[1, 2]}    | ${[1]}
    ${[1, 2]}    | ${[1]}       | ${[1]}
    ${[2]}       | ${[1]}       | ${[]}
    ${[2, 3]}    | ${[1, 2]}    | ${[2]}
    ${[2, 3, 1]} | ${[7, 3, 2]} | ${[2, 3]}
    ${[3, 4]}    | ${[1, 2]}    | ${[]}
  `('the biggest subset of $a2 build from $a1 is $expected', ({a1, a2, expected}) => {
    const sortedResult = ensureSubset(a1, a2).sort();
    const sortedExpected = expected.sort();
    expect(sortedResult).toEqual(sortedExpected);
  });

  test.each`
    a1        | a2        | expected
    ${[]}     | ${[]}     | ${[]}
    ${[1]}    | ${[]}     | ${[1]}
    ${[]}     | ${[1]}    | ${[1]}
    ${[1]}    | ${[2]}    | ${[1, 2]}
    ${[1, 2]} | ${[2]}    | ${[1, 2]}
    ${[1, 2]} | ${[1, 2]} | ${[1, 2]}
    ${[1, 2]} | ${[2, 3]} | ${[3, 2, 1]}
    ${[1, 2]} | ${[3, 4]} | ${[1, 2, 3, 4]}
  `('the smallest superset of $a2 build from $a1 is $expected', ({a1, a2, expected}) => {
    const sortedResult = ensureSuperset(a1, a2).sort();
    const sortedExpected = expected.sort();
    expect(sortedResult).toEqual(sortedExpected);
  });

  test.each`
    permissions                                  | changes                           | expectedConsistentPermissions
    ${{view: [], edit: [], own: []}}             | ${{type: 'view', values: []}}     | ${{view: [], edit: [], own: []}}
    ${{view: [], edit: [], own: []}}             | ${{type: 'view', values: [1]}}    | ${{view: [1], edit: [], own: []}}
    ${{view: [], edit: [], own: []}}             | ${{type: 'edit', values: [1]}}    | ${{view: [1], edit: [1], own: []}}
    ${{view: [], edit: [], own: []}}             | ${{type: 'own', values: [1]}}     | ${{view: [1], edit: [1], own: [1]}}
    ${{view: [1], edit: [2], own: [2]}}          | ${{type: 'view', values: [2]}}    | ${{view: [2], edit: [2], own: [2]}}
    ${{view: [1], edit: [2], own: [2]}}          | ${{type: 'view', values: [1, 2]}} | ${{view: [2, 1], edit: [2], own: [2]}}
    ${{view: [1], edit: [2], own: [2]}}          | ${{type: 'view', values: [1]}}    | ${{view: [1], edit: [], own: []}}
    ${{view: [1, 2], edit: [1], own: []}}        | ${{type: 'view', values: [1, 3]}} | ${{view: [1, 3], edit: [1], own: []}}
    ${{view: [1, 2], edit: [1], own: []}}        | ${{type: 'edit', values: [3]}}    | ${{view: [1, 2, 3], edit: [3], own: []}}
    ${{view: [1, 2], edit: [1, 2], own: [1, 2]}} | ${{type: 'edit', values: [1, 3]}} | ${{view: [1, 2, 3], edit: [1, 3], own: [1]}}
    ${{view: [1], edit: [1], own: [1]}}          | ${{type: 'own', values: [1, 2]}}  | ${{view: [1, 2], edit: [1, 2], own: [1, 2]}}
    ${{view: [1], edit: [1, 2], own: [1]}}       | ${{type: 'own', values: [3]}}     | ${{view: [1, 2, 3], edit: [1, 2, 3], own: [3]}}
  `(
    'consistent permissions build from {view:$permissions.view,edit:$permissions.edit,own:$permissions.own} by applying changes {type:$changes.type, values:$changes.values} is : {view:$expectedConsistentPermissions.view,edit:$expectedConsistentPermissions.edit,own:$expectedConsistentPermissions.own}',
    ({permissions, changes, expectedConsistentPermissions}) => {
      const result = alterPermissionsConsistently(permissions, changes);
      result.view.sort();
      result.edit.sort();
      result.own.sort();
      expectedConsistentPermissions.view.sort();
      expectedConsistentPermissions.edit.sort();
      expectedConsistentPermissions.own.sort();

      expect(result.view).toEqual(expectedConsistentPermissions.view);
      expect(result.edit).toEqual(expectedConsistentPermissions.edit);
      expect(result.own).toEqual(expectedConsistentPermissions.own);
    }
  );
});
