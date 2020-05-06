import React from 'react';
import { Operator } from './Operator';
import {
  FamilyConditionLine,
  FamilyConditionLineProps
} from '../pages/EditRules/components/conditions/FamilyConditionLine';
import { ConditionFactoryType } from './Condition';
import {
  getFamiliesByIdentifiers,
  IndexedFamilies,
} from '../fetch/FamilyFetcher';
import { Router } from '../dependenciesTools';

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
  families: IndexedFamilies;
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
    json.field === 'family' &&
    operatorIsValid(json.operator) &&
    jsonValueIsValid(json.value)
  );
};

const createFamilyCondition: ConditionFactoryType = async (
  json: any,
  router: Router
): Promise<FamilyCondition | null> => {
  if (!familyConditionPredicate(json)) {
    return null;
  }

  const families = json.value
    ? await getFamiliesByIdentifiers(json.value, router)
    : {};
  if (Object.values(families).length !== (json.value || []).length) {
    return null;
  }

  return {
    module: FamilyConditionLine,
    field: 'family',
    operator: json.operator,
    value: json.value,
    families,
  };
};

export { FamilyCondition, createFamilyCondition, FamilyOperators };
