import { Router } from '../../dependenciesTools';
import React from 'react';
import { ConditionLineProps } from '../../pages/EditRules/components/conditions/ConditionLineProps';
import { FallbackConditionLine } from '../../pages/EditRules/components/conditions/FallbackConditionLine';
import {
  Condition,
  getAssetCollectionAttributeConditionModule,
  getBooleanAttributeConditionModule,
  getCategoryConditionModule,
  getCompletenessConditionModule,
  getDateAttributeConditionModule,
  getDateSystemConditionModule,
  getEntityTypeConditionModule,
  getFamilyConditionModule,
  getFamilyVariantConditionModule,
  getFileAttributeConditionModule,
  getGroupsConditionModule,
  getIdentifierAttributeCondtionModule,
  getIdentifierConditionModule,
  getMeasurementAttributeConditionModule,
  getNumberAttributeConditionModule,
  getPictureAttributeConditionModule,
  getPimConditionModule,
  getPriceCollectionAttributeConditionModule,
  getSimpleMultiOptionsAttributeConditionModule,
  getSimpleMultiReferenceEntitiesAttributeConditionModule,
  getStatusConditionModule,
  getTextareaAttributeConditionModule,
  getTextAttributeConditionModule,
} from './';

export type ConditionModuleGuesser = (
  json: any,
  router: Router
) => Promise<React.FC<ConditionLineProps & { condition: Condition }> | null>;

const getConditionModule: (
  json: any,
  router: Router
) => Promise<React.FC<ConditionLineProps & { condition: Condition }>> = async (
  json,
  router
) => {
  const getConditionModuleFunctions: ConditionModuleGuesser[] = [
    getFamilyConditionModule,
    getFamilyVariantConditionModule,
    getIdentifierConditionModule,
    getCategoryConditionModule,
    getGroupsConditionModule,
    getIdentifierAttributeCondtionModule,
    getStatusConditionModule,
    getPictureAttributeConditionModule,
    getEntityTypeConditionModule,
    getFileAttributeConditionModule,
    getTextAttributeConditionModule,
    getTextareaAttributeConditionModule,
    getSimpleMultiOptionsAttributeConditionModule,
    getBooleanAttributeConditionModule,
    getNumberAttributeConditionModule,
    getCompletenessConditionModule,
    getDateSystemConditionModule,
    getSimpleMultiReferenceEntitiesAttributeConditionModule,
    getAssetCollectionAttributeConditionModule,
    getPriceCollectionAttributeConditionModule,
    getMeasurementAttributeConditionModule,
    getDateAttributeConditionModule,
    // Fallback
    getPimConditionModule,
  ];

  for (let i = 0; i < getConditionModuleFunctions.length; i++) {
    const getModuleFunction = getConditionModuleFunctions[i];
    const module = await getModuleFunction(json, router);
    if (module !== null) {
      return module;
    }
  }

  return FallbackConditionLine;
};

export { getConditionModule };
