import { Router } from '../../dependenciesTools';
import { getAttributeByIdentifier } from '../../repositories/AttributeRepository';
import { BooleanAttributeConditionLine } from '../../pages/EditRules/components/conditions/BooleanAttributeConditionLine';
import { Operator } from '../Operator';
import { ConditionFactory } from './Condition';
import { ConditionModuleGuesser } from './ConditionModuleGuesser';

const TYPE = 'pim_catalog_boolean';

const BooleanAttributeOperators = [Operator.EQUALS, Operator.NOT_EQUAL];

type BooleanAttributeCondition = {
  field: string;
  operator: Operator;
  value: boolean;
  scope?: string;
  locale?: string;
};

const createBooleanAttributeCondition: ConditionFactory = async (
  fieldCode: string,
  router: Router
): Promise<BooleanAttributeCondition | null> => {
  const attribute = await getAttributeByIdentifier(fieldCode, router);
  if (null === attribute || attribute.type !== TYPE) {
    return null;
  }

  return {
    field: fieldCode,
    operator: Operator.EQUALS,
    value: false,
  };
};

const getBooleanAttributeConditionModule: ConditionModuleGuesser = async (
  json,
  router
) => {
  if (typeof json.field !== 'string') {
    return null;
  }

  if (
    typeof json.operator !== 'string' ||
    !BooleanAttributeOperators.includes(json.operator)
  ) {
    return null;
  }

  const attribute = await getAttributeByIdentifier(json.field, router);
  if (null === attribute || attribute.type !== TYPE) {
    return null;
  }

  return BooleanAttributeConditionLine;
};

export {
  BooleanAttributeOperators,
  BooleanAttributeCondition,
  getBooleanAttributeConditionModule,
  createBooleanAttributeCondition,
};
