import { Group, GroupCode } from '../models';
import { Router } from '../dependenciesTools';
import { fetchGroupsByIdentifiers, IndexedGroups } from '../fetch/GroupFetcher';

const cachedGroups: { [groupCode: string]: Group | null } = {};

const getGroupsByIdentifiers = async (
  groupIdentifiers: GroupCode[],
  router: Router
): Promise<IndexedGroups> => {
  const results: IndexedGroups = {};

  const groupIdentifiersToFetch: GroupCode[] = [];
  groupIdentifiers.forEach((identifier: GroupCode) => {
    if (typeof cachedGroups[identifier] !== 'undefined') {
      results[identifier] = cachedGroups[identifier] as Group;
    } else {
      groupIdentifiersToFetch.push(identifier);
    }
  });

  if (groupIdentifiersToFetch.length > 0) {
    const fetchResults = await fetchGroupsByIdentifiers(
      groupIdentifiersToFetch,
      router
    );
    groupIdentifiersToFetch.forEach((groupIdentifier: GroupCode): void => {
      if (typeof fetchResults[groupIdentifier] !== 'undefined') {
        results[groupIdentifier] = fetchResults[groupIdentifier];
        cachedGroups[groupIdentifier] = fetchResults[groupIdentifier];
      } else {
        cachedGroups[groupIdentifier] = null;
      }
    });
  }

  return results;
};

const getGroupByIdentifier = async (
  groupIdentifier: GroupCode,
  router: Router
): Promise<Group> => {
  const group = await getGroupsByIdentifiers([groupIdentifier], router);
  return group[groupIdentifier];
};

export { getGroupsByIdentifiers, getGroupByIdentifier };
