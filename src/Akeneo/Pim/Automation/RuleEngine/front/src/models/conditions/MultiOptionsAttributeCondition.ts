import { Router } from '../../dependenciesTools';
import { getAttributeByIdentifier } from '../../repositories/AttributeRepository';
import { Operator } from '../Operator';
import { ConditionFactory } from './Condition';
import { MultiOptionsAttributeConditionLine } from '../../pages/EditRules/components/conditions/MultiOptionsAttributeConditionLine';
import { ConditionModuleGuesser } from './ConditionModuleGuesser';

const TYPE = 'pim_catalog_multiselect';

const MultiOptionsAttributeOperators = [
  Operator.IN_LIST,
  Operator.IS_EMPTY,
  Operator.IS_NOT_EMPTY,
];

type MultiOptionsAttributeCondition = {
  scope?: string;
  field: string;
  operator: Operator;
  value?: string[];
  locale?: string;
};

const createMultiOptionsAttributeCondition: ConditionFactory = async (
  fieldCode: string,
  router: Router
): Promise<MultiOptionsAttributeCondition | null> => {
  const attribute = await getAttributeByIdentifier(fieldCode, router);
  if (null === attribute || attribute.type !== TYPE) {
    return null;
  }

  return {
    field: fieldCode,
    operator: Operator.IS_EMPTY,
  };
};

const getMultiOptionsAttributeConditionModule: ConditionModuleGuesser = async (
  json,
  router
) => {
  if (typeof json.field !== 'string') {
    return null;
  }

  if (
    typeof json.operator !== 'string' ||
    !MultiOptionsAttributeOperators.includes(json.operator)
  ) {
    return null;
  }

  const attribute = await getAttributeByIdentifier(json.field, router);
  if (null === attribute || attribute.type !== TYPE) {
    return null;
  }

  return MultiOptionsAttributeConditionLine;
};

export {
  MultiOptionsAttributeOperators,
  MultiOptionsAttributeCondition,
  getMultiOptionsAttributeConditionModule,
  createMultiOptionsAttributeCondition,
};
