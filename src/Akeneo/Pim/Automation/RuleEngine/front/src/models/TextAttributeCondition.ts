import React from 'react';
import { Attribute, validateLocalizableScopableAttribute } from './Attribute';
import { Router } from '../dependenciesTools';
import { getAttributeByIdentifier } from '../repositories/AttributeRepository';
import {
  TextAttributeConditionLine,
  TextAttributeConditionLineProps,
} from '../pages/EditRules/components/conditions/TextAttributeConditionLine';
import { Operator } from './Operator';
import {
  checkLocaleIsBoundToScope,
  checkScopeExists,
} from '../repositories/ScopeRepository';
import { ConditionFactoryType } from './Condition';
import { checkLocaleExists } from '../repositories/LocaleRepository';

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

  const attribute = await getAttributeByIdentifier(json.field, router);
  if (null === attribute) {
    return null;
  }

  if (attribute.type === TYPE) {
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

export {
  TextAttributeOperators,
  TextAttributeCondition,
  denormalizeTextAttributeCondition,
};
