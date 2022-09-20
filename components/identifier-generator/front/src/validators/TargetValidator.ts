import {Validator} from './Validator';
import {Target} from '../models';
import {Violation} from './Violation';

const validateTarget: Validator<Target> = (target, path) => {
  const violations: Violation[] = [];

  if (target.trim() === '') {
    violations.push({path, message: 'Target should not be empty'});
  }

  return violations;
};

export {validateTarget};
