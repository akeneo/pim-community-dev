import {Validator} from './Validator';
import {Enabled} from '../models';
import {Violation} from './Violation';

const validateEnabled: Validator<Enabled> = (enabled, path) => {
  const violations: Violation[] = [];

  if (typeof enabled.value === 'undefined') {
    violations.push({
      path: `${path}.value`,
      message: 'The value should not be undefined',
    });
  }

  return violations;
};

export {validateEnabled};
