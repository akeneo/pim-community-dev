import {cloneDeep, filter} from 'lodash/fp';
import {CategoryPermission, CategoryPermissions} from '../models/CategoryPermission';
import {UserGroup} from '../hooks/useFetchUserGroups';

interface PermissionChanges {
  type: keyof CategoryPermissions;
  values: number[];
}

/**
 * Make a copy of array permissions1 where all elements that are not in permission2 are removed
 *
 * The logic is to keep only the permissions that are in the permissions2 that are also in the permissions1.
 * In that way we remove all the element in permissions1 that aren't in permissions2.
 *
 * @param permissions1 The array that should be a subset of permission2
 * @param permission2
 * @return a copy of permissions1 eventually modified so that it's a subset of permission2
 */
export function ensureSubset(
  permissions1: CategoryPermission[],
  permission2: CategoryPermission[]
): CategoryPermission[] {
  return filter(
    (permission: CategoryPermission) => permission2.map(permission => permission.id).includes(permission.id),
    permissions1
  );
}

/**
 * Make a copy of array permissions1 where all elements of permissions2 are present
 *
 * The logic is to add all elements from permissions1 and keep only those from permisions2 which aren't in permission1.
 *
 * @param permissions1 The array that should be a superset of permissions2
 * @param permissions2
 * @return a copy of permissions1 eventually modified so that it's a super of permissions2
 */
export function ensureSuperset(
  permissions1: CategoryPermission[],
  permissions2: CategoryPermission[]
): CategoryPermission[] {
  return [
    ...permissions1,
    ...filter(
      (permission: CategoryPermission) => !permissions1.map(permission => permission.id).includes(permission.id),
      permissions2
    ),
  ];
}

/**
 * Permissions are a set of 3 number set : view, edit and own
 * The invariant is :
 * - the set 'own' is included in the set 'edit'
 * - AND the set 'edit' is included in the set 'view'
 *
 * This function produce permissions from base permissions
 * Ensuring the requested changes are honored
 * and the invrarient is preserved
 * by doing the minimum amount of modifications
 * @param userGroups
 * @param permissions
 * @param changes
 * @returns
 */
export function alterPermissionsConsistently(
  userGroups: UserGroup[],
  permissions: CategoryPermissions,
  changes: PermissionChanges
): CategoryPermissions {
  const {type, values} = changes;
  let consistentPermissions = cloneDeep(permissions);
  // the change that MUST be made
  consistentPermissions[type] = userGroups
    .filter(userGroup => values.includes(parseInt(userGroup.id, 10)))
    .map(userGroup => ({
      id: parseInt(userGroup.id, 10),
      label: userGroup.label,
    }));

  // now adapting other permission level in accordance
  switch (type) {
    case 'view':
      consistentPermissions.edit = ensureSubset(permissions['edit'], consistentPermissions.view);
      consistentPermissions.own = ensureSubset(permissions['own'], consistentPermissions.edit);
      break;
    case 'edit':
      consistentPermissions.view = ensureSuperset(permissions['view'], consistentPermissions.edit);
      consistentPermissions.own = ensureSubset(permissions['own'], consistentPermissions.edit);
      break;
    case 'own':
      consistentPermissions.edit = ensureSuperset(permissions['edit'], consistentPermissions.own);
      consistentPermissions.view = ensureSuperset(permissions['view'], consistentPermissions.edit);
      break;
  }

  return consistentPermissions;
}
