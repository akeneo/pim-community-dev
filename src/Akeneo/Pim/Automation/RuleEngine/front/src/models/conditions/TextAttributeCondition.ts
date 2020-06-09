import { Router } from '../../dependenciesTools';
import { getAttributeByIdentifier } from '../../repositories/AttributeRepository';
import { TextAttributeConditionLine } from '../../pages/EditRules/components/conditions/TextAttributeConditionLine';
import { Operator } from '../Operator';
import { ConditionFactory } from './Condition';
import { ConditionModuleGuesser } from './ConditionModuleGuesser';

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
    field: fieldCode,
    operator: Operator.IS_EMPTY,
  };
};

const getTextAttributeConditionModule: ConditionModuleGuesser = async (
  json,
  router
) => {
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
  if (null === attribute || attribute.type !== TYPE) {
    return null;
  }

  return TextAttributeConditionLine;
};

export {
  TextAttributeOperators,
  TextAttributeCondition,
  getTextAttributeConditionModule,
  createTextAttributeCondition,
};
