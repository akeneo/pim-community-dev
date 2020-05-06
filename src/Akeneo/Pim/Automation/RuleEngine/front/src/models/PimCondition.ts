/**
 * A PimCondition is a condition but not coded for now.
 * Its difference with the fallback is that it can be have its renderer.
 * Each native condition coming from the PIM has the same fields.
 */
import {
  PimConditionLine,
  PimConditionLineProps,
} from '../pages/EditRules/components/conditions/PimConditionLine';
import React from 'react';
import { ConditionDenormalizer } from './Condition';

type PimCondition = {
  module: React.FC<PimConditionLineProps>;
  field: string;
  operator: string;
  value: any | null;
  locale: string | null;
  scope: string | null;
};

export const denormalizePimCondition: ConditionDenormalizer = async (
  json: any
): Promise<PimCondition | null> => {
  if (
    typeof json.field === 'string' &&
    typeof json.operator === 'string' // TODO check operator
  ) {
    return Promise.resolve({
      module: PimConditionLine,
      field: json.field,
      operator: json.operator,
      value: json.value,
      locale: json.locale,
      scope: json.scope,
    });
  }

  return null;
};

export { PimCondition };
