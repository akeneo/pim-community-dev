import React from 'react';
import { Attribute } from '../Attribute';
import { Router } from '../../dependenciesTools';
import { getAttributeByIdentifier } from '../../repositories/AttributeRepository';
import {
  TextAttributeConditionLine,
  TextAttributeConditionLineProps,
} from '../../pages/EditRules/components/conditions/TextAttributeConditionLine';
import { Operator } from '../Operator';
import { ConditionDenormalizer, ConditionFactory } from './Condition';

const TYPE = 'pim_catalog_text';

const TextAttributeOperators = [
  Operator.EQUALS,
  Operator.NOT_EQUAL,
  Operator.CONTAINS,
  Operator.STARTS_WITH,
  Operator.DOES_NOT_CONTAIN,
  Operator.IS_EMPTY,
  Operator.IS_NOT_EMPTY,
];

type TextAttributeCondition = {
  scope?: string;
  module: React.FC<TextAttributeConditionLineProps>;
  attribute: Attribute;
  field: string;
  operator: Operator;
  value?: string;
  locale?: string;
};

const createTextAttributeCondition: ConditionFactory = async (
  fieldCode: string,
  router: Router
): Promise<TextAttributeCondition | null> => {
  const attribute = await getAttributeByIdentifier(fieldCode, router);
  if (null === attribute || attribute.type !== TYPE) {
    return null;
  }

  return {
    module: TextAttributeConditionLine,
    attribute,
    field: fieldCode,
    operator: Operator.IS_EMPTY,
  };
};

const denormalizeTextAttributeCondition: ConditionDenormalizer = async (
  json: any,
  router: Router
): Promise<TextAttributeCondition | null> => {
  if (typeof json.field !== 'string') {
    return null;
  }

  if (
    typeof json.operator !== 'string' ||
    !TextAttributeOperators.includes(json.operator)
  ) {
    return null;
  }

  const textAttributeCondition = await createTextAttributeCondition(
    json.field,
    router
  );
  if (textAttributeCondition === null) {
    return null;
  }

  return {
    ...(textAttributeCondition as TextAttributeCondition),
    operator: json.operator,
    value: json.value,
    locale: json.locale || null,
    scope: json.scope || null,
  };
};

export {
  TextAttributeOperators,
  TextAttributeCondition,
  denormalizeTextAttributeCondition,
  createTextAttributeCondition,
};
