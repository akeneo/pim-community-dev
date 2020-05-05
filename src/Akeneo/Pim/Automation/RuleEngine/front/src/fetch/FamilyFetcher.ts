import { httpGet } from './fetch';
import { Family } from '../models';
import { Router } from '../dependenciesTools';

type IndexedFamilies = { [familyCode: string]: Family };

const cacheFamilies: { [familyCode: string]: Family | null } = {};

const fetchFamiliesByIdentifiers = async (
  familyIdentifiers: string[],
  router: Router
): Promise<IndexedFamilies> => {
  const url = router.generate('pim_enrich_family_rest_index', {
    identifiers: familyIdentifiers.join(','),
    options: {
      expanded: 0,
    },
  });
  const response = await httpGet(url);

  return response.status === 404 ? null : await response.json();
};

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

export { getFamiliesByIdentifiers, IndexedFamilies };
