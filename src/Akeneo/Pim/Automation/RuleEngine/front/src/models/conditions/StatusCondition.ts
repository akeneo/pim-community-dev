import {Operator} from '../Operator';
import {ConditionFactory} from './Condition';
import {ConditionModuleGuesser} from './ConditionModuleGuesser';
import {StatusConditionLine} from '../../pages/EditRules/components/conditions/StatusConditionLine';

const FIELD = 'enabled';

const StatusOperators: Operator[] = [Operator.EQUALS, Operator.NOT_EQUAL];

type StatusCondition = {
  field: string;
  operator: Operator;
  value: boolean;
};

const operatorIsValid = (operator: any): boolean => {
  return (
    typeof operator === 'string' &&
    StatusOperators.includes(operator as Operator)
  );
};

const statusConditionPredicate = (json: any): boolean => {
  return (
    json.field === FIELD &&
    operatorIsValid(json.operator) &&
    typeof json.value === 'boolean'
  );
};

const getStatusConditionModule: ConditionModuleGuesser = json => {
  if (!statusConditionPredicate(json)) {
    return Promise.resolve<null>(null);
  }

  return Promise.resolve(StatusConditionLine);
};

const createStatusCondition: ConditionFactory = (
  fieldCode: any
): Promise<StatusCondition | null> => {
  if (fieldCode !== FIELD) {
    return Promise.resolve<null>(null);
  }

  return Promise.resolve<StatusCondition>({
    field: FIELD,
    operator: StatusOperators[0],
    value: true,
  });
};

export {
  StatusCondition,
  createStatusCondition,
  StatusOperators,
  getStatusConditionModule,
};
