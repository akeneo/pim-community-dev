import React from "react";
import {Attribute} from "./Attribute";
import {Condition} from "./Condition";
import {Router } from "../dependenciesTools";
import {getAttributeByIdentifier} from "../fetch/AttributeFetcher";
import {TextAttributeConditionLine} from "../pages/EditRules/TextAttributeConditionLine";
import {Operator} from "./Operator";
import {ConditionLineProps} from "../pages/EditRules/ConditionLineProps";

const TextAttributeOperators = [
  Operator.EQUALS,
  Operator.NOT_EQUAL,
  Operator.CONTAINS,
  Operator.START_WITH,
  Operator.DOES_NOT_CONTAIN,
  Operator.IS_EMPTY,
  Operator.IS_NOT_EMPTY,
];

type TextAttributeCondition = {
  scope?: string;
  module: React.FC<ConditionLineProps & { condition: Condition }>,
  attribute: Attribute;
  operator: Operator;
  value?: string;
  locale?: string;
}

const createTextAttributeCondition = async (json: any, router: Router): Promise <TextAttributeCondition | null> => {
  if (typeof json.field !== 'string') {
    return null;
  }

  if (typeof json.operator !== 'string' || !TextAttributeOperators.includes(json.operator)) {
    return null;
  }

  const attribute = await getAttributeByIdentifier(json.field, router);
  if (null === attribute) {
    return null;
  }

  if (attribute.type === 'pim_catalog_text') {
    return {
      module: TextAttributeConditionLine,
      attribute,
      operator: json.operator,
      value: json.value,
      locale: json.locale,
      scope: json.scope,
    };
  }

  return null;
};

export {TextAttributeOperators, TextAttributeCondition, createTextAttributeCondition};
