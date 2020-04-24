import React from 'react';
import { Attribute, validateLocalizableScopableAttribute } from './Attribute';
import { Router } from '../dependenciesTools';
import { getAttributeByIdentifier } from '../fetch/AttributeFetcher';
import {
  TextAttributeConditionLine,
  TextAttributeConditionLineProps
} from '../pages/EditRules/TextAttributeConditionLine';
import { Operator } from './Operator';
import { checkLocaleExists } from '../fetch/LocaleFetcher';
import {
  checkLocaleIsBoundToScope,
  checkScopeExists,
} from '../fetch/ScopeFetcher';

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

const createTextAttributeCondition = async (
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

  const attribute = await getAttributeByIdentifier(json.field, router);
  if (null === attribute) {
    return null;
  }

  if (attribute.type === 'pim_catalog_text') {
    const localeCode = json.locale || null;
    const scopeCode = json.scope || null;

    if (
      !(await checkLocaleExists(localeCode, router)) ||
      !(await checkScopeExists(scopeCode, router)) ||
      !(await checkLocaleIsBoundToScope(localeCode, scopeCode, router)) ||
      !validateLocalizableScopableAttribute(attribute, localeCode, scopeCode)
    ) {
      return null;
    }

    return {
      module: TextAttributeConditionLine,
      attribute,
      field: json.field,
      operator: json.operator,
      value: json.value,
      locale: localeCode,
      scope: scopeCode,
    };
  }

  return null;
};

export {
  TextAttributeOperators,
  TextAttributeCondition,
  createTextAttributeCondition,
};
