import React from 'react';
import { Operator } from '../Operator';
import {
  FamilyConditionLine,
  FamilyConditionLineProps,
} from '../../pages/EditRules/components/conditions/FamilyConditionLine';
import { ConditionDenormalizer, ConditionFactory } from './Condition';

const FIELD = 'family';

const FamilyOperators = [
  Operator.IS_EMPTY,
  Operator.IS_NOT_EMPTY,
  Operator.IN_LIST,
  Operator.NOT_IN_LIST,
];

type FamilyCondition = {
  module: React.FC<FamilyConditionLineProps>;
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

const denormalizeFamilyCondition: ConditionDenormalizer = (
  json: any
): Promise<FamilyCondition | null> => {
  if (!familyConditionPredicate(json)) {
    return Promise.resolve<null>(null);
  }

  return Promise.resolve<FamilyCondition>({
    module: FamilyConditionLine,
    field: FIELD,
    operator: json.operator,
    value: json.value,
  });
};

const createFamilyCondition: ConditionFactory = (
  fieldCode: any
): Promise<FamilyCondition | null> => {
  if (fieldCode !== FIELD) {
    return Promise.resolve<null>(null);
  }

  return Promise.resolve<FamilyCondition>({
    module: FamilyConditionLine,
    field: FIELD,
    operator: Operator.IN_LIST,
    value: [],
  });
};

export {
  FamilyCondition,
  denormalizeFamilyCondition,
  createFamilyCondition,
  FamilyOperators,
};
