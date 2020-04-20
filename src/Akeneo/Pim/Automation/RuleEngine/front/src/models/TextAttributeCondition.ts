import React from "react";
import {Attribute} from "./Attribute";
import {Condition} from "./Condition";
import {Router, Translate} from "../dependenciesTools";
import {getByIdentifier} from "../fetch/AttributeFetcher";
import {TextAttributeConditionLine} from "../pages/EditRules/TextAttributeConditionLine";

enum TextAttributeOperator {
  IS_EMPTY = 'EMPTY',
  IS_NOT_EMPTY = 'NOT EMPTY',
  IN_LIST = 'IN',
  NOT_IN_LIST = 'NOT IN',
}

type TextAttributeCondition = {
  module: React.FC<{register: any, condition: Condition, lineNumber: number, translate: Translate}>,
  attribute: Attribute;
  operator: TextAttributeOperator;
  value?: string;
}

const createTextAttributeCondition = async (json: any, router: Router): Promise <TextAttributeCondition | null> => {
  if (typeof json.field !== 'string') {
    return null;
  }

  const attribute = await getByIdentifier(json.field, router);
  if (attribute.type === 'pim_catalog_text') {
    return {
      module: TextAttributeConditionLine,
      attribute,
      operator: json.operator,
      value: json.value
    };
  }

  return null;
};

export {TextAttributeCondition, createTextAttributeCondition};
