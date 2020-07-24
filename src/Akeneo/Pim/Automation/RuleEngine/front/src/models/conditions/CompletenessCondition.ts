import { Operator } from "../Operator";
import { ScopeCode } from "../Scope";
import { ConditionModuleGuesser } from "./ConditionModuleGuesser";
import { CompletenessConditionLine } from "../../pages/EditRules/components/conditions/CompletenessConditionLine";
import { ConditionFactory } from "./Condition";

const CompletenessOperators: Operator[] = [
  Operator.EQUALS,
  Operator.NOT_EQUAL,
  Operator.LOWER_THAN,
  Operator.GREATER_THAN,
];

export type CompletenessCondition = {
  scope: ScopeCode;
  field: string;
  operator: Operator;
  value: number;
  locale?: string;
};

const getCompletenessConditionModule: ConditionModuleGuesser = json => {
  if (json.field !== 'completeness') {
    return Promise.resolve<null>(null);
  }
  if (!CompletenessOperators.includes(json.operator)) {
    return Promise.resolve<null>(null);
  }

  return Promise.resolve(CompletenessConditionLine);
};

const createCompletenessCondition: ConditionFactory = (
  fieldCode: any
): Promise<CompletenessCondition | null> => {
  if (fieldCode !== 'completeness') {
    return Promise.resolve<null>(null);
  }

  return Promise.resolve<CompletenessCondition>({
    field: 'completeness',
    operator: Operator.EQUALS,
    value: 0,
    locale: '',
    scope: '',
  });
};

export { getCompletenessConditionModule, createCompletenessCondition, CompletenessOperators }
