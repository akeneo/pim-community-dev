import {cloneDeep, filter} from 'lodash/fp';
import {CategoryPermissions} from '../models';

interface PermissionChanges {
  type: keyof CategoryPermissions;
  values: number[];
}

/**
 * Make a copy of array a1 where all elements that are not in a2 are removed
 * @param a1 The array that should be a subset of a2
 * @param a2
 * @return a copy of a1 eventually modified so that it's a subset of a2
 */
export function ensureSubset(a1: number[], a2: number[]): number[] {
  return filter((n: number) => a2.includes(n), a1);
}

/**
 * Make a copy of array a1 where all elements of a2 are present
 * @param a1 The array that should be a superset of a2
 * @param a2
 * @return a copy of a1 eventually modified so that it's a super of a2
 */
export function ensureSuperset(a1: number[], a2: number[]): number[] {
  return [...a1, ...filter((n: number) => !a1.includes(n), a2)];
}

function getAddedValue(originalValues: number[], changedValues: number[]) {
  return changedValues.filter(changedValue => !originalValues.includes(changedValue));
}

function getRemovedValue(originalValues: number[], changedValues: number[]) {
  return originalValues.filter(originalValue => !changedValues.includes(originalValue));
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
 * @param permissions
 * @param changes
 * @returns
 */
export function alterPermissionsConsistently(
  permissions: CategoryPermissions,
  changes: PermissionChanges
): CategoryPermissions {
  const {type, values} = changes;
  let consistentPermissions = cloneDeep(permissions);
  // the change that MUST be made
  consistentPermissions[type] = values;

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
