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
import { getDateAttributeConditionModule } from './DateAttributeCondition';
import { FallbackConditionLine } from '../../pages/EditRules/components/conditions/FallbackConditionLine';
import { getCompletenessConditionModule } from './CompletenessCondition';
import { getGroupsConditionModule } from './GroupCondition';
import { getStatusConditionModule } from './StatusCondition';
import { getDateSystemConditionModule } from './DateSystemCondition';
import { getBooleanAttributeConditionModule } from './BooleanAttributeCondition';

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
    getGroupsConditionModule,
    getStatusConditionModule,
    getTextAttributeConditionModule,
    getSimpleMultiOptionsAttributeConditionModule,
    getBooleanAttributeConditionModule,
    getNumberAttributeConditionModule,
    getCompletenessConditionModule,
    getDateSystemConditionModule,
    // Fallback
    getDateAttributeConditionModule,
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
