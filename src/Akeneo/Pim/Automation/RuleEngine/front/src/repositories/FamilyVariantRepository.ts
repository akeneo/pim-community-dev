import {FamilyCode} from '../models';
import {Router} from '../dependenciesTools';
import {FamilyVariant, FamilyVariantCode} from '../models/FamilyVariant';
import {
  fetchFamilyVariantsByIdentifiers,
  IndexedFamilyVariants,
} from '../fetch/FamilyVariantFetcher';

const cachedFamilyVariants: {
  [familyVariantCode: string]: FamilyVariant | null;
} = {};

const getFamilyVariantsByIdentifiers = async (
  familyVariantIdentifiers: FamilyVariantCode[],
  router: Router
): Promise<IndexedFamilyVariants> => {
  const results: IndexedFamilyVariants = {};

  const familyVariantIdentifiersToFetch: FamilyVariantCode[] = [];
  familyVariantIdentifiers.forEach((identifier: FamilyVariantCode) => {
    if (typeof cachedFamilyVariants[identifier] !== 'undefined') {
      results[identifier] = cachedFamilyVariants[identifier] as FamilyVariant;
    } else {
      familyVariantIdentifiersToFetch.push(identifier);
    }
  });

  if (familyVariantIdentifiersToFetch.length > 0) {
    const fetchResults = await fetchFamilyVariantsByIdentifiers(
      familyVariantIdentifiersToFetch,
      router
    );
    familyVariantIdentifiersToFetch.forEach(
      (familyVariantIdentifier: FamilyCode): void => {
        if (typeof fetchResults[familyVariantIdentifier] !== 'undefined') {
          results[familyVariantIdentifier] =
            fetchResults[familyVariantIdentifier];
          cachedFamilyVariants[familyVariantIdentifier] =
            fetchResults[familyVariantIdentifier];
        } else {
          cachedFamilyVariants[familyVariantIdentifier] = null;
        }
      }
    );
  }

  return results;
};

const getFamilyVariantByIdentifier = async (
  familyVariantIdentifier: FamilyVariantCode,
  router: Router
): Promise<FamilyVariant> => {
  const familyVariant = await getFamilyVariantsByIdentifiers(
    [familyVariantIdentifier],
    router
  );
  return familyVariant[familyVariantIdentifier];
};

export {getFamilyVariantsByIdentifiers, getFamilyVariantByIdentifier};
