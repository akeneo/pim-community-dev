import {Operator} from '../Operator';
import {FamilyConditionLine} from '../../pages/EditRules/components/conditions/FamilyConditionLine';
import {ConditionFactory} from './Condition';
import {ConditionModuleGuesser} from './ConditionModuleGuesser';

const FIELD = 'family';

const FamilyOperators = [
  Operator.IN_LIST,
  Operator.NOT_IN_LIST,
  Operator.IS_EMPTY,
  Operator.IS_NOT_EMPTY,
];

type FamilyCondition = {
  field: string;
  operator: Operator;
  value: string[];
};

const operatorIsValid = (operator: any): boolean => {
  return (
    typeof operator === 'string' &&
    FamilyOperators.includes(operator as Operator)
  );
};

const jsonValueIsValid = (value: any): boolean => {
  return typeof value === 'undefined' || value === null || Array.isArray(value);
};

const familyConditionPredicate = (json: any): boolean => {
  return (
    json.field === FIELD &&
    operatorIsValid(json.operator) &&
    jsonValueIsValid(json.value)
  );
};

const getFamilyConditionModule: ConditionModuleGuesser = json => {
  if (!familyConditionPredicate(json)) {
    return Promise.resolve<null>(null);
  }

  return Promise.resolve(FamilyConditionLine);
};

const createFamilyCondition: ConditionFactory = (
  fieldCode: any
): Promise<FamilyCondition | null> => {
  if (fieldCode !== FIELD) {
    return Promise.resolve<null>(null);
  }

  return Promise.resolve<FamilyCondition>({
    field: FIELD,
    operator: FamilyOperators[0],
    value: [],
  });
};

export {
  FamilyCondition,
  createFamilyCondition,
  FamilyOperators,
  getFamilyConditionModule,
};
