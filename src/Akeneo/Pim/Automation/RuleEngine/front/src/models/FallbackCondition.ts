import React from "react";
import {FallbackConditionLine} from "../pages/EditRules/FallbackConditionLine";
import {Condition} from "./Condition";
import {Translate} from "../dependenciesTools/provider/applicationDependenciesProvider.type";

export type FallbackCondition = {
  module: React.FC<{condition: Condition, translate: Translate}>,
  json: any;
}

export const createFallbackCondition = (json: any) : FallbackCondition => {
  return {
    module: FallbackConditionLine,
    json: json
  };
};
