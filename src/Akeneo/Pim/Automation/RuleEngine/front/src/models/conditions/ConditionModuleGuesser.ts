import { Router } from '../../dependenciesTools';
import React from 'react';
import { ConditionLineProps } from '../../pages/EditRules/components/conditions/ConditionLineProps';
import { Condition } from './Condition';
import { getFamilyConditionModule } from './FamilyCondition';
import { getCategoryConditionModule } from './CategoryCondition';
import { getTextAttributeConditionModule } from './TextAttributeCondition';
import { getSimpleMultiOptionsAttributeConditionModule } from './SimpleMultiOptionsAttributeCondition';
import { getNumberAttributeConditionModule } from './NumberAttributeCondition';
import { getPimConditionModule } from './PimCondition';
import { FallbackConditionLine } from '../../pages/EditRules/components/conditions/FallbackConditionLine';
import { getCompletenessConditionModule } from "./CompletenessCondition";

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
    getCategoryConditionModule,
    getTextAttributeConditionModule,
    getSimpleMultiOptionsAttributeConditionModule,
    getNumberAttributeConditionModule,
    getCompletenessConditionModule,
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
