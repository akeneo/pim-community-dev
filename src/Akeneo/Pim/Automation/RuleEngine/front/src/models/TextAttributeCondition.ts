import React from "react";
import {Attribute} from "./Attribute";
import {Condition} from "./Condition";
import {Router } from "../dependenciesTools";
import {getAttributeByIdentifier} from "../fetch/AttributeFetcher";
import {TextAttributeConditionLine} from "../pages/EditRules/TextAttributeConditionLine";
import {Operator} from "./Operator";
import {Locale} from "./Locale";
import {getActivatedLocaleByCode} from "../fetch/LocaleFetcher";
import {ConditionLineProps} from "../pages/EditRules/ConditionLineProps";
import {Scope} from "./Scope";

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
  scope?: Scope;
  module: React.FC<ConditionLineProps & { condition: Condition }>,
  attribute: Attribute;
  operator: Operator;
  value?: string;
  locale?: Locale;
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
    let result: TextAttributeCondition = {
      module: TextAttributeConditionLine,
      attribute,
      operator: json.operator,
      value: json.value,
      locale: undefined
    };

    if (json.hasOwnProperty('locale')) {
      const locale = await getActivatedLocaleByCode(json.locale, router);
      if (locale) {
        result.locale = locale;
      }
    }

    return result;
  }

  return null;
};

export {TextAttributeOperators, TextAttributeCondition, createTextAttributeCondition};
