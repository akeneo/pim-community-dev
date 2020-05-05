import React from 'react';
import { FallbackConditionLine } from '../pages/EditRules/components/conditions/FallbackConditionLine';
import { ConditionLineProps } from '../pages/EditRules/components/conditions/ConditionLineProps';

export type FallbackCondition = {
  module: React.FC<ConditionLineProps>;
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
