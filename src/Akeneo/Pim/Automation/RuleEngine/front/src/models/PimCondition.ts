/**
 * A PimCondition is a condition but not coded for now.
 * Its difference with the fallback is that it can be have its renderer.
 * Each native condition coming from the PIM has the same fields.
 */
import {PimConditionLine} from "../pages/EditRules/PimConditionLine";
import React from "react";
import {Condition} from "./Condition";
import {Translate} from "../dependenciesTools/provider/applicationDependenciesProvider.type";

type PimCondition = {
  module: React.FC<{condition: Condition, translate: Translate}>,
  field: string;
  operator: string;
  value: any|null;
  locale: string|null;
  scope: string|null;
}

export const createPimCondition = (json: any) : PimCondition | null => {
  if (typeof json.field === 'string' &&
    typeof json.operator === 'string' // TODO check operator
  ) {
    return {
      module: PimConditionLine,
      field: json.field,
      operator: json.operator,
      value: json.value,
      locale: json.locale,
      scope: json.scope
    };
  }

  return null;
};

export { PimCondition }
