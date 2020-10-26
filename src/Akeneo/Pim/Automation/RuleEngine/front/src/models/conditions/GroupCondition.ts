import {Operator} from '../Operator';
import {GroupsConditionLine} from '../../pages/EditRules/components/conditions/GroupsConditionLine';
import {ConditionFactory} from './Condition';
import {ConditionModuleGuesser} from './ConditionModuleGuesser';

const FIELD = 'groups';

const GroupOperators = [
  Operator.IN_LIST,
  Operator.NOT_IN_LIST,
  Operator.IS_EMPTY,
  Operator.IS_NOT_EMPTY,
];

type GroupsCondition = {
  field: string;
  operator: Operator;
  value: string[];
};

const operatorIsValid = (operator: any): boolean => {
  return (
    typeof operator === 'string' &&
    GroupOperators.includes(operator as Operator)
  );
};

const jsonValueIsValid = (value: any): boolean => {
  return typeof value === 'undefined' || value === null || Array.isArray(value);
};

const groupsConditionPredicate = (json: any): boolean => {
  return (
    json.field === FIELD &&
    operatorIsValid(json.operator) &&
    jsonValueIsValid(json.value)
  );
};

const getGroupsConditionModule: ConditionModuleGuesser = json => {
  if (!groupsConditionPredicate(json)) {
    return Promise.resolve<null>(null);
  }

  return Promise.resolve(GroupsConditionLine);
};

const createGroupsCondition: ConditionFactory = (
  fieldCode: any
): Promise<GroupsCondition | null> => {
  if (fieldCode !== FIELD) {
    return Promise.resolve<null>(null);
  }

  return Promise.resolve<GroupsCondition>({
    field: FIELD,
    operator: GroupOperators[0],
    value: [],
  });
};

export {
  GroupsCondition,
  createGroupsCondition,
  GroupOperators,
  getGroupsConditionModule,
};
