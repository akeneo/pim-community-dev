import {Family, FamilyCode} from '../models';
import {Router} from '../dependenciesTools';
import {
  fetchFamiliesByIdentifiers,
  IndexedFamilies,
} from '../fetch/FamilyFetcher';

const cacheFamilies: {[familyCode: string]: Family | null} = {};

const getFamiliesByIdentifiers = async (
  familyIdentifiers: FamilyCode[],
  router: Router
): Promise<IndexedFamilies> => {
  const results: IndexedFamilies = {};

  const familyIdentifiersToFetch: FamilyCode[] = [];
  familyIdentifiers.forEach((identifier: FamilyCode) => {
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
    familyIdentifiersToFetch.forEach((familyIdentifier: FamilyCode): void => {
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

const getFamilyByIdentifier = async (
  familyIdentifier: FamilyCode,
  router: Router
): Promise<Family> => {
  const family = await getFamiliesByIdentifiers([familyIdentifier], router);
  return family[familyIdentifier];
};

export {getFamiliesByIdentifiers, getFamilyByIdentifier};
