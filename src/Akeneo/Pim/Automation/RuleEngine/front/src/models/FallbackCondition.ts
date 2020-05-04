import React from 'react';
import { FallbackConditionLine } from '../pages/EditRules/FallbackConditionLine';
import { ConditionLineProps } from '../pages/EditRules/ConditionLineProps';

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
