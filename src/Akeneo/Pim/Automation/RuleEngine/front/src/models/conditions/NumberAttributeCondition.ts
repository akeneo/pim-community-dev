import { Router } from '../../dependenciesTools';
import { getAttributeByIdentifier } from '../../repositories/AttributeRepository';
import { NumberAttributeConditionLine } from '../../pages/EditRules/components/conditions/NumberAttributeConditionLine';
import { Operator } from '../Operator';
import { ConditionFactory } from './Condition';
import { ConditionModuleGuesser } from './ConditionModuleGuesser';

const TYPE = 'pim_catalog_number';

const NumberAttributeOperators = [
  Operator.EQUALS,
  Operator.NOT_EQUAL,
  Operator.LESS,
  Operator.LESS_OR_EQUAL,
  Operator.MORE,
  Operator.MORE_OR_EQUAL,
  Operator.IS_EMPTY,
  Operator.IS_NOT_EMPTY,
];

type NumberAttributeCondition = {
  scope?: string;
  field: string;
  operator: Operator;
  value?: string;
  locale?: string;
};

const createNumberAttributeCondition: ConditionFactory = async (
  fieldCode: string,
  router: Router
): Promise<NumberAttributeCondition | null> => {
  const attribute = await getAttributeByIdentifier(fieldCode, router);
  if (null === attribute || attribute.type !== TYPE) {
    return null;
  }

  return {
    field: fieldCode,
    operator: Operator.IS_EMPTY,
  };
};

const getNumberAttributeConditionModule: ConditionModuleGuesser = async (
  json,
  router
) => {
  if (typeof json.field !== 'string') {
    return null;
  }

  if (
    typeof json.operator !== 'string' ||
    !NumberAttributeOperators.includes(json.operator)
  ) {
    return null;
  }

  const attribute = await getAttributeByIdentifier(json.field, router);
  if (null === attribute || attribute.type !== TYPE) {
    return null;
  }

  return NumberAttributeConditionLine;
};

export {
  NumberAttributeOperators,
  NumberAttributeCondition,
  getNumberAttributeConditionModule,
  createNumberAttributeCondition,
};
