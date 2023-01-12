import {Operator, SimpleSelectCondition} from '../models';
import {Violation} from './Violation';
import {Validator} from './Validator';

const validateSimpleSelect: Validator<SimpleSelectCondition> = (condition, path) => {
  const violations: Violation[] = [];

  if ([Operator.IN, Operator.NOT_IN].includes(condition?.operator) && condition.value?.length === 0) {
    violations.push({
      path,
      message: `A value is required for the ${condition.label} attribute`,
    });
  }

  if (condition?.scopable && !condition.scope) {
    violations.push({
      path,
      message: `A channel is required for the ${condition.label} attribute`,
    });
  }

  if (condition?.localizable && !condition.locale) {
    violations.push({
      path,
      message: `A locale is required for the ${condition.label} attribute`,
    });
  }

  return violations;
};

export {validateSimpleSelect};
