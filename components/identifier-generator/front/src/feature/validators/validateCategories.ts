import {Validator} from './Validator';
import {CategoriesCondition, CategoriesOperators, Operator} from '../models';
import {Violation} from './Violation';

const validateCategories: Validator<CategoriesCondition> = (categoriesCondition, path) => {
  const violations: Violation[] = [];

  if (!CategoriesOperators.includes(categoriesCondition.operator)) {
    violations.push({
      path: `${path}.operator`,
      message: `The operator should be one of the following: ${CategoriesOperators.join(', ')}`,
    });
  }

  if (
    categoriesCondition.operator === Operator.IN ||
    categoriesCondition.operator === Operator.NOT_IN ||
    categoriesCondition.operator === Operator.IN_CHILDREN_LIST ||
    categoriesCondition.operator === Operator.NOT_IN_CHILDREN_LIST
  ) {
    if (typeof categoriesCondition.value === 'undefined') {
      violations.push({
        path: `${path}.value`,
        message: 'The value should be defined',
      });
    } else {
      if (categoriesCondition.value.length === 0) {
        violations.push({
          path: `${path}.value`,
          message: 'You should filter with at least one category',
        });
      }
    }
  }

  if (categoriesCondition.operator === Operator.UNCLASSIFIED || categoriesCondition.operator === Operator.CLASSIFIED) {
    if (typeof categoriesCondition.value !== 'undefined') {
      violations.push({
        path: `${path}.value`,
        message: 'The value should not be defined',
      });
    }
  }

  const unknownProperties = Object.keys(categoriesCondition).filter(k => !['type', 'value', 'operator'].includes(k));
  if (unknownProperties.length > 0) {
    violations.push({
      path: `${path}`,
      message: `The following properties are unknown: ${unknownProperties.join(', ')}`,
    });
  }

  return violations;
};

export {validateCategories};
