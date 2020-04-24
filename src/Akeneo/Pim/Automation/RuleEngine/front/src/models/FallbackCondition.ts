import React from 'react';
import {FallbackConditionLine, FallbackConditionLineProps} from '../pages/EditRules/FallbackConditionLine';

export type FallbackCondition = {
  module: React.FC<FallbackConditionLineProps>;
  json: any;
};

export const createFallbackCondition = async (
  json: any
): Promise<FallbackCondition> => {
  return {
    module: FallbackConditionLine,
    json: json,
  };
};
