import { Router } from '../../dependenciesTools';
import { getAttributeByIdentifier } from '../../repositories/AttributeRepository';
import { Operator } from '../Operator';
import { ConditionFactory } from './Condition';
import { SimpleMultiOptionsAttributeConditionLine } from '../../pages/EditRules/components/conditions/SimpleMultiOptionsAttributeConditionLine';
import { ConditionModuleGuesser } from './ConditionModuleGuesser';

const TYPES = ['pim_catalog_simpleselect', 'pim_catalog_multiselect'];

const SimpleMultiOptionsAttributeOperators = [
  Operator.IN_LIST,
  Operator.NOT_IN_LIST,
  Operator.IS_EMPTY,
  Operator.IS_NOT_EMPTY,
];

type SimpleMultiOptionsAttributeCondition = {
  scope?: string;
  field: string;
  operator: Operator;
  value?: string[];
  locale?: string;
};

const createSimpleMultiOptionsAttributeCondition: ConditionFactory = async (
  fieldCode: string,
  router: Router
): Promise<SimpleMultiOptionsAttributeCondition | null> => {
  const attribute = await getAttributeByIdentifier(fieldCode, router);
  if (null === attribute || !TYPES.includes(attribute.type)) {
    return null;
  }

  return {
    field: fieldCode,
    operator: Operator.IS_EMPTY,
  };
};

const getSimpleMultiOptionsAttributeConditionModule: ConditionModuleGuesser = async (
  json,
  router
) => {
  if (typeof json.field !== 'string') {
    return null;
  }

  if (
    typeof json.operator !== 'string' ||
    !SimpleMultiOptionsAttributeOperators.includes(json.operator)
  ) {
    return null;
  }

  const attribute = await getAttributeByIdentifier(json.field, router);
  if (null === attribute || !TYPES.includes(attribute.type)) {
    return null;
  }

  return SimpleMultiOptionsAttributeConditionLine;
};

export {
  SimpleMultiOptionsAttributeOperators,
  SimpleMultiOptionsAttributeCondition,
  getSimpleMultiOptionsAttributeConditionModule,
  createSimpleMultiOptionsAttributeCondition,
};
