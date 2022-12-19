import {Validator} from './Validator';
import {EnabledCondition} from '../models';
import {Violation} from './Violation';

const validateEnabled: Validator<EnabledCondition> = (enabledCondition, path) => {
  const violations: Violation[] = [];

  if (typeof enabledCondition.value === 'undefined') {
    violations.push({
      path: `${path}.value`,
      message: 'The value should not be undefined',
    });
  }

  const unknownProperties = Object.keys(enabledCondition).filter(k => !['type', 'value'].includes(k));
  if (unknownProperties.length > 0) {
    violations.push({
      path: `${path}`,
      message: `The following properties are unknown: ${unknownProperties.join(', ')}`,
    });
  }

  return violations;
};

export {validateEnabled};
