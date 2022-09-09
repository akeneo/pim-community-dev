import {cloneDeep, identity, isEqual, sortBy} from 'lodash/fp';

import {LabelCollection} from '@akeneo-pim-community/shared';
import {CategoryAttributes, CategoryPermissions, CategoryProperties, EnrichCategory} from '../models';

function labelsAreEqual(l1: LabelCollection, l2: LabelCollection): boolean {
  // maybe too strict of simplistic, to adjust
  return isEqual(l1, l2);
}

const sort = sortBy<number>(identity);

function isEqualUnordered(a1: number[], a2: number[]): boolean {
  return isEqual(sort(a1), sort(a2));
}

export function permissionsAreEqual(cp1: CategoryPermissions, cp2: CategoryPermissions): boolean {
  return (
    isEqualUnordered(cp1.view, cp2.view) && isEqualUnordered(cp1.edit, cp2.edit) && isEqualUnordered(cp1.own, cp2.own)
  );
}

function attributesAreEqual(a1: CategoryAttributes, a2: CategoryAttributes): boolean {
  // maybe too strict of simplistic, to adjust
  return isEqual(a1, a2);
}

function propertiesAreEqual(p1: CategoryProperties, p2: CategoryProperties): boolean {
  return p1.code === p2.code && labelsAreEqual(p1.labels, p2.labels);
}

export function categoriesAreEqual(c1: EnrichCategory, c2: EnrichCategory): boolean {
  return (
    c1.id === c2.id &&
    propertiesAreEqual(c1.properties, c2.properties) &&
    permissionsAreEqual(c1.permissions, c2.permissions) &&
    attributesAreEqual(c1.attributes, c2.attributes)
  );
}

/**
 * Ensure no category field is null.
 */
export function normalizeCategory(category: EnrichCategory): EnrichCategory {
  const normalized = cloneDeep(category);
  if (category.permissions === null) {
    normalized.permissions = {view: [], edit: [], own: []};
  }
  if (category.attributes === null) {
    // TODO use fetched template to populate default values here
    normalized.attributes = {};
  }
  if (category.properties.labels === null) {
    normalized.properties.labels = {};
  }
  return normalized;
}
