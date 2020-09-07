import { Operator } from '../Operator';
import { ConditionFactory } from './Condition';
import {
  createAttributeCondition,
  getAttributeConditionModule,
} from './AbstractAttributeCondition';
import { AttributeType } from '../Attribute';
import { ConditionModuleGuesser } from './ConditionModuleGuesser';
import { MeasurementAttributeConditionLine } from '../../pages/EditRules/components/conditions/MeasurementAttributeConditionLine';

const MeasurementAttributeOperators = [
  Operator.EQUALS,
  Operator.NOT_EQUAL,
  Operator.LOWER_THAN,
  Operator.LOWER_OR_EQUAL_THAN,
  Operator.GREATER_THAN,
  Operator.GREATER_OR_EQUAL_THAN,
  Operator.IS_EMPTY,
  Operator.IS_NOT_EMPTY,
];

type MeasurementAttributeCondition = {
  scope?: string;
  field: string;
  operator: Operator;
  value?: {
    amount: number;
    unit: string;
  };
  locale?: string;
};

const createMeasurementAttributeCondition: ConditionFactory = async (
  fieldCode,
  router
) => {
  return createAttributeCondition(
    fieldCode,
    router,
    [AttributeType.METRIC],
    Operator.EQUALS
  );
};

const getMeasurementAttributeConditionModule: ConditionModuleGuesser = async (
  json,
  router
) => {
  return getAttributeConditionModule(
    json,
    router,
    MeasurementAttributeOperators,
    [AttributeType.METRIC],
    MeasurementAttributeConditionLine
  );
};

export {
  MeasurementAttributeOperators,
  MeasurementAttributeCondition,
  getMeasurementAttributeConditionModule,
  createMeasurementAttributeCondition,
};
