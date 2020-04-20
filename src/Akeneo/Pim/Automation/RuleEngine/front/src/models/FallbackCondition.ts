import React from "react";
import {FallbackConditionLine} from "../pages/EditRules/FallbackConditionLine";
import {Condition} from "./Condition";
import {Translate} from "../dependenciesTools/provider/applicationDependenciesProvider.type";

export type FallbackCondition = {
  module: React.FC<{register: any, condition: Condition, lineNumber: number, translate: Translate}>,
  json: any;
}

export const createFallbackCondition = async (json: any) : Promise <FallbackCondition> => {
  return {
    module: FallbackConditionLine,
    json: json
  };
};
