import {Operator, SimpleOrMultiSelectCondition} from '../models';
import {Violation} from './Violation';
import {Validator} from './Validator';

const validateSimpleOrMultiSelect: Validator<SimpleOrMultiSelectCondition> = (condition, path) => {
  const violations: Violation[] = [];

  if ([Operator.IN, Operator.NOT_IN].includes(condition?.operator) && condition.value?.length === 0) {
    violations.push({
      path,
      message: `A value is required for the ${condition.attributeCode} attribute`,
    });
  }

  return violations;
};

export {validateSimpleOrMultiSelect};
