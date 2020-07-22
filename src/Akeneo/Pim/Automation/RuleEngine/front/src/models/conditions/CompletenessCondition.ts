import { Operator } from "../Operator";
import { ScopeCode } from "../Scope";
import { ConditionModuleGuesser } from "./ConditionModuleGuesser";
import { CompletenessConditionLine } from "../../pages/EditRules/components/conditions/CompletenessConditionLine";
import { ConditionFactory } from "./Condition";

const CompletenessOperators = [
  Operator.EQUALS,
  Operator.NOT_EQUAL,
  Operator.LOWER_THAN,
  Operator.LOWER_OR_EQUAL_THAN,
  Operator.GREATER_THAN,
  Operator.GREATER_OR_EQUAL_THAN,
  Operator.NOT_EQUALS_ON_AT_LEAST_ONE_LOCALE,
  Operator.EQUALS_ON_AT_LEAST_ONE_LOCALE,
  Operator.GREATER_THAN_ON_AT_LEAST_ONE_LOCALE,
  Operator.GREATER_OR_EQUALS_THAN_ON_AT_LEAST_ONE_LOCALE,
  Operator.LOWER_OR_EQUALS_THAN_ON_AT_LEAST_ONE_LOCALE,
  Operator.LOWER_THAN_ON_AT_LEAST_ONE_LOCALE,
];

const CompletenessOperatorsCompatibility = new Map<Operator, Operator>([
  [ Operator.EQUALS, Operator.EQUALS_ON_AT_LEAST_ONE_LOCALE ],
  [ Operator.NOT_EQUAL, Operator.NOT_EQUALS_ON_AT_LEAST_ONE_LOCALE ],
  [ Operator.LOWER_THAN, Operator.LOWER_THAN_ON_AT_LEAST_ONE_LOCALE ],
  [ Operator.LOWER_OR_EQUAL_THAN, Operator.LOWER_OR_EQUALS_THAN_ON_AT_LEAST_ONE_LOCALE ],
  [ Operator.GREATER_THAN, Operator.GREATER_THAN_ON_AT_LEAST_ONE_LOCALE ],
  [ Operator.GREATER_OR_EQUAL_THAN, Operator.GREATER_OR_EQUALS_THAN_ON_AT_LEAST_ONE_LOCALE ]
]);

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
    value: 100,
    locale: '',
    scope: '',
  });
};

export { getCompletenessConditionModule, createCompletenessCondition, CompletenessOperators, CompletenessOperatorsCompatibility }
