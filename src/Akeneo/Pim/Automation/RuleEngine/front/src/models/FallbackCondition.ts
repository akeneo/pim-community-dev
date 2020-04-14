import React from "react";
import {FallbackConditionLine} from "../pages/EditRules/FallbackConditionLine";
import {Condition} from "./Condition";

export type FallbackCondition = {
  module: React.FC<{condition: Condition}>,
  json: any;
}

export const createFallbackCondition = (json: any) : FallbackCondition => {
  return {
    module: FallbackConditionLine,
    json: json
  };
};
