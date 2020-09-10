import { httpGet } from './fetch';
import { Router } from '../dependenciesTools';
import { FamilyVariant } from '../models/FamilyVariant';

type IndexedFamilyVariants = { [familyVariantCode: string]: FamilyVariant };

const fetchFamilyVariantsByIdentifiers = async (
  familyVariantIdentifiers: string[],
  router: Router
): Promise<IndexedFamilyVariants> => {
  const url = router.generate('pim_enrich_family_variant_rest_index', {
    identifiers: familyVariantIdentifiers.join(','),
    options: {
      expanded: 0,
    },
  });
  const response = await httpGet(url);

  return response.status === 404 ? null : await response.json();
};

export { fetchFamilyVariantsByIdentifiers, IndexedFamilyVariants };
