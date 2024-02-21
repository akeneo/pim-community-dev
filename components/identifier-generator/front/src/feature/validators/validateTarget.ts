import {Validator} from './Validator';
import {Target} from '../models';
import {Violation} from './Violation';

const validateTarget: Validator<Target | undefined> = (target, path) => {
  const violations: Violation[] = [];

  if ((target || '').trim() === '') {
    violations.push({path, message: 'The target must be filled'});
  }

  return violations;
};

export {validateTarget};
