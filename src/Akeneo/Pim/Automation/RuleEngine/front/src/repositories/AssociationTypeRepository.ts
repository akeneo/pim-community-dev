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

const getAssociationTypesFromQuantified = async (
  router: Router,
  isQuantified: boolean
): Promise<AssociationType[]> => {
  const associationTypes = await getAllAssociationTypes(router);

  return associationTypes.filter(
    associationType => associationType.is_quantified === isQuantified
  );
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

export {
  getAllAssociationTypes,
  getAssociationTypesFromQuantified,
  getAssociationTypeByCode,
};
