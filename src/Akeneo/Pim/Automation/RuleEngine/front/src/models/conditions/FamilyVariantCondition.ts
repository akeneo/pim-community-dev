import { Operator } from '../Operator';
import { ConditionFactory } from './Condition';
import { ConditionModuleGuesser } from './ConditionModuleGuesser';
import { FamilyVariantConditionLine } from '../../pages/EditRules/components/conditions/FamilyVariantConditionLine';
import { FamilyVariantCode } from '../';

const FIELD = 'family_variant';

const FamilyVariantOperators = [
  Operator.IN_LIST,
  Operator.NOT_IN_LIST,
  Operator.IS_EMPTY,
  Operator.IS_NOT_EMPTY,
];

type FamilyVariantCondition = {
  field: 'family_variant';
  operator: Operator;
  value: FamilyVariantCode[];
};

const operatorIsValid = (operator: any): boolean => {
  return (
    typeof operator === 'string' &&
    FamilyVariantOperators.includes(operator as Operator)
  );
};

const jsonValueIsValid = (value: any): boolean => {
  return typeof value === 'undefined' || value === null || Array.isArray(value);
};

const familyVariantConditionPredicate = (json: any): boolean => {
  return (
    json.field === FIELD &&
    operatorIsValid(json.operator) &&
    jsonValueIsValid(json.value)
  );
};

const getFamilyVariantConditionModule: ConditionModuleGuesser = json => {
  if (!familyVariantConditionPredicate(json)) {
    return Promise.resolve<null>(null);
  }

  return Promise.resolve(FamilyVariantConditionLine);
};

const createFamilyVariantCondition: ConditionFactory = (
  fieldCode: any
): Promise<FamilyVariantCondition | null> => {
  if (fieldCode !== FIELD) {
    return Promise.resolve<null>(null);
  }

  return Promise.resolve<FamilyVariantCondition>({
    field: FIELD,
    operator: FamilyVariantOperators[0],
    value: [],
  });
};

export {
  FamilyVariantCondition,
  createFamilyVariantCondition,
  FamilyVariantOperators,
  getFamilyVariantConditionModule,
};
