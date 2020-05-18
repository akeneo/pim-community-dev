import { Family } from '../models';
import { Router } from '../dependenciesTools';
import {
  fetchFamiliesByIdentifiers,
  IndexedFamilies,
} from '../fetch/FamilyFetcher';

const cacheFamilies: { [familyCode: string]: Family | null } = {};

const getFamiliesByIdentifiers = async (
  familyIdentifiers: string[],
  router: Router
): Promise<IndexedFamilies> => {
  const results: IndexedFamilies = {};

  const familyIdentifiersToFetch: string[] = [];
  familyIdentifiers.forEach((identifier: string) => {
    if (typeof cacheFamilies[identifier] !== 'undefined') {
      results[identifier] = cacheFamilies[identifier] as Family;
    } else {
      familyIdentifiersToFetch.push(identifier);
    }
  });

  if (familyIdentifiersToFetch.length > 0) {
    const fetchResults = await fetchFamiliesByIdentifiers(
      familyIdentifiersToFetch,
      router
    );
    familyIdentifiersToFetch.forEach((familyIdentifier: string): void => {
      if (typeof fetchResults[familyIdentifier] !== 'undefined') {
        results[familyIdentifier] = fetchResults[familyIdentifier];
        cacheFamilies[familyIdentifier] = fetchResults[familyIdentifier];
      } else {
        cacheFamilies[familyIdentifier] = null;
      }
    });
  }

  return results;
};

export { getFamiliesByIdentifiers };
