import React from 'react';
import { FallbackConditionLine } from '../pages/EditRules/FallbackConditionLine';
import { Condition } from './Condition';
import { ConditionLineProps } from '../pages/EditRules/ConditionLineProps';

export type FallbackCondition = {
  module: React.FC<ConditionLineProps & { condition: Condition }>;
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
