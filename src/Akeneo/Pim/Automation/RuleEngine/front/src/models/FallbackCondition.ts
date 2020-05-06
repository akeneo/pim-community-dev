import React from 'react';
import {
  FallbackConditionLine,
  FallbackConditionLineProps
} from '../pages/EditRules/components/conditions/FallbackConditionLine';

export type FallbackCondition = {
  module: React.FC<FallbackConditionLineProps>;
  json: any;
};

export const createFallbackCondition = async (
  json: any
): Promise<FallbackCondition> => {
  return Promise.resolve({
    module: FallbackConditionLine,
    json: json,
  });
};
