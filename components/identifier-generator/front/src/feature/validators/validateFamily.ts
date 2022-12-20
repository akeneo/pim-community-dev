import {Validator} from './Validator';
import {FamilyCondition, FamilyOperators, Operator} from '../models';
import {Violation} from './Violation';

const validateFamily: Validator<FamilyCondition> = (familyCondition, path) => {
  const violations: Violation[] = [];

  if (!FamilyOperators.includes(familyCondition.operator)) {
    violations.push({
      path: `${path}.operator`,
      message: `The operator should be one of the following: ${FamilyOperators.join(', ')}`,
    });
  }

  if (familyCondition.operator === Operator.IN || familyCondition.operator === Operator.NOT_IN) {
    if (typeof familyCondition.value === 'undefined') {
      violations.push({
        path: `${path}.value`,
        message: 'The value should be defined',
      });
    } else {
      if (familyCondition.value.length === 0) {
        violations.push({
          path: `${path}.value`,
          message: 'You should filter with at least one family',
        });
      }
    }
  }

  if (familyCondition.operator === Operator.EMPTY || familyCondition.operator === Operator.NOT_EMPTY) {
    if (typeof familyCondition.value !== 'undefined') {
      violations.push({
        path: `${path}.value`,
        message: 'The value should not be defined',
      });
    }
  }

  const unknownProperties = Object.keys(familyCondition).filter(k => !['type', 'value', 'operator'].includes(k));
  if (unknownProperties.length > 0) {
    violations.push({
      path: `${path}`,
      message: `The following properties are unknown: ${unknownProperties.join(', ')}`,
    });
  }

  return violations;
};

export {validateFamily};
