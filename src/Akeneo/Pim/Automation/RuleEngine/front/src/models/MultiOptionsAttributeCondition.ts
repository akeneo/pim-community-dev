import React from 'react';
import { Attribute } from './Attribute';
import { Router } from '../dependenciesTools';
import { getAttributeByIdentifier } from '../repositories/AttributeRepository';
import { Operator } from './Operator';
import { ConditionDenormalizer, ConditionFactory } from './Condition';
import {
  MultiOptionsAttributeConditionLine,
  MultiOptionsAttributeConditionLineProps
} from "../pages/EditRules/components/conditions/MultiOptionsAttributeConditionLine";

const TYPE = 'pim_catalog_multiselect';

const MultiOptionsAttributeOperators = [
  Operator.IN_LIST,
  Operator.IS_EMPTY,
  Operator.IS_NOT_EMPTY,
];

type MultiOptionsAttributeCondition = {
  scope?: string;
  module: React.FC<MultiOptionsAttributeConditionLineProps>;
  attribute: Attribute;
  field: string;
  operator: Operator;
  value?: string[];
  locale?: string;
};

const createMultiOptionsAttributeCondition: ConditionFactory = async (
  fieldCode: string,
  router: Router
): Promise<MultiOptionsAttributeCondition | null> => {
  const attribute = await getAttributeByIdentifier(fieldCode, router);
  if (null === attribute || attribute.type !== TYPE) {
    return null;
  }

  return {
    module: MultiOptionsAttributeConditionLine,
    attribute,
    field: fieldCode,
    operator: Operator.IS_EMPTY,
  };
};

const denormalizeMultiOptionsAttributeCondition: ConditionDenormalizer = async (
  json: any,
  router: Router
): Promise<MultiOptionsAttributeCondition | null> => {
  if (typeof json.field !== 'string') {
    return null;
  }

  if (
    typeof json.operator !== 'string' ||
    !MultiOptionsAttributeOperators.includes(json.operator)
  ) {
    return null;
  }

  const multiOptionsAttributeCondition = await createMultiOptionsAttributeCondition(
    json.field,
    router
  );
  if (multiOptionsAttributeCondition === null) {
    return null;
  }

  return {
    ...(multiOptionsAttributeCondition as MultiOptionsAttributeCondition),
    operator: json.operator,
    value: json.value,
    locale: json.locale || null,
    scope: json.scope || null,
  };
};

export {
  MultiOptionsAttributeOperators,
  MultiOptionsAttributeCondition,
  denormalizeMultiOptionsAttributeCondition,
  createMultiOptionsAttributeCondition,
};
