import { Group, GroupCode } from '../models';
import { Router } from '../dependenciesTools';
import { fetchGroupsByIdentifiers } from '../fetch/GroupFetcher';

const cachedGroups: { [groupCode: string]: Group | null } = {};

const getCachedGroups = (
  groupCodes: GroupCode[]
): { [groupCode: string]: Group | null } => {
  const results: { [groupCode: string]: Group | null } = {};
  Object.keys(cachedGroups)
    .filter(key => groupCodes.includes(key))
    .forEach((groupCode: string) => {
      results[groupCode] = cachedGroups[groupCode];
    });

  return results;
};

const getGroupsByIdentifiers = async (
  groupIdentifiers: GroupCode[],
  router: Router
): Promise<{ [groupCode: string]: Group | null }> => {
  const results = getCachedGroups(groupIdentifiers);
  const groupIdentifiersToFetch: GroupCode[] = groupIdentifiers.filter(
    key => !Object.keys(cachedGroups).includes(key)
  );

  if (groupIdentifiersToFetch.length > 0) {
    const fetchedResults = await fetchGroupsByIdentifiers(
      groupIdentifiersToFetch,
      router
    );
    groupIdentifiersToFetch.forEach((groupIdentifier: GroupCode): void => {
      if (typeof fetchedResults[groupIdentifier] !== 'undefined') {
        results[groupIdentifier] = fetchedResults[groupIdentifier];
        cachedGroups[groupIdentifier] = fetchedResults[groupIdentifier];
      } else {
        cachedGroups[groupIdentifier] = null;
      }
    });
  }

  return results;
};

export { getGroupsByIdentifiers };
