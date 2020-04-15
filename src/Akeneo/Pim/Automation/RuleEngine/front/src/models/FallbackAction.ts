import React from "react";
import {FallbackActionLine} from "../pages/EditRules/components/FallbackActionLine";
import {Translate} from "../dependenciesTools";

export type FallbackAction = {
  module: React.FC<{ action: FallbackAction, translate: Translate }>,
  json: any;
}

export const createFallbackAction = (json: any): FallbackAction => {
  return {
    module: FallbackActionLine,
    json
  }
};
