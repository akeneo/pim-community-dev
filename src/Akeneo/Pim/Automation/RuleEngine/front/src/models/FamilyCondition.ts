// src/Akeneo/Pim/Enrichment/Component/Product/Query/Filter/Operators.php
import {FallbackConditionLine} from "../pages/EditRules/FallbackConditionLine";
import {Condition} from "./Condition";
import React from "react";
import {Translate} from "../dependenciesTools/provider/applicationDependenciesProvider.type";

enum FamilyOperator {
  IS_EMPTY = 'EMPTY',
  IS_NOT_EMPTY = 'NOT EMPTY',
  IN_LIST = 'IN',
  NOT_IN_LIST = 'NOT IN',
}

type FamilyCondition = {
  module: React.FC<{register: any, condition: Condition, lineNumber: number, translate: Translate}>,
  operator: FamilyOperator;
  familyCodes: string[];
}

const createFamilyCondition = async (json: any): Promise <FamilyCondition | null> => {
  // TODO Remove this line when we implement family condition.
  return null;

  if (json.field !== 'family') {
    return null;
  }
  if (![FamilyOperator.IS_EMPTY, FamilyOperator.IS_NOT_EMPTY, FamilyOperator.IN_LIST, FamilyOperator.NOT_IN_LIST].includes(json.operator)) {
    return null;
  }

  return {
    module: FallbackConditionLine,
    operator: json.operator,
    familyCodes: json.value
  };
};

export { FamilyCondition, createFamilyCondition }
