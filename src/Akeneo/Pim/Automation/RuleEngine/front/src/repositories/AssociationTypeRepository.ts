import { AssociationType } from '../models/';
import { Router } from '../dependenciesTools';
import { fetchAllAssociationTypes } from '../fetch/AssociationTypeFetcher';

let cachedAssociationTypes: AssociationType[] | undefined;

const getAllAssociationTypes = async (
  router: Router
): Promise<AssociationType[]> => {
  if (!cachedAssociationTypes) {
    cachedAssociationTypes = await fetchAllAssociationTypes(router);
  }

  return cachedAssociationTypes;
};

export { getAllAssociationTypes };
