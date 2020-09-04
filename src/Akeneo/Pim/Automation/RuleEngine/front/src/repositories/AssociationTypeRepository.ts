import { AssociationType, AssociationTypeCode } from '../models/';
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

const getAssociationTypeByCode = async (
  code: AssociationTypeCode,
  router: Router
): Promise<AssociationType | undefined> => {
  return (await getAllAssociationTypes(router)).find(
    (associationType: AssociationType) => {
      return associationType.code === code;
    }
  );
};

export { getAllAssociationTypes, getAssociationTypeByCode };
