import React from 'react';
import { Router } from '../../dependenciesTools';
import { getAttributeByIdentifier } from '../../repositories/AttributeRepository';
import { AttributeType } from '../Attribute';
import { Operator } from '../Operator';
import { Condition } from './Condition';
import { ConditionLineProps } from '../../pages/EditRules/components/conditions/ConditionLineProps';

const createAttributeCondition = async (
  fieldCode: string,
  router: Router,
  attributeTypes: AttributeType[],
  defaultOperator: Operator,
  defaultValue?: any
): Promise<Condition | null> => {
  const attribute = await getAttributeByIdentifier(fieldCode, router);
  if (null === attribute || !attributeTypes.includes(attribute.type)) {
    return null;
  }

  return {
    field: fieldCode,
    operator: defaultOperator,
    value: defaultValue,
  };
};

const getAttributeConditionModule = async (
  json: any,
  router: Router,
  operators: Operator[],
  attributeTypes: AttributeType[],
  module: React.FC<ConditionLineProps & { condition: Condition }>
) => {
  if (typeof json.field !== 'string') {
    return null;
  }

  if (typeof json.operator !== 'string' || !operators.includes(json.operator)) {
    return null;
  }

  const attribute = await getAttributeByIdentifier(json.field, router);
  if (null === attribute || !attributeTypes.includes(attribute.type)) {
    return null;
  }

  return module;
};

export { createAttributeCondition, getAttributeConditionModule };
