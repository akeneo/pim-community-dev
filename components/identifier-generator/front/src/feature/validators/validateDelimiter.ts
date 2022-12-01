import {Validator} from './Validator';
import {Delimiter} from '../models';
import {Violation} from './Violation';

const validateDelimiter: Validator<Delimiter | null> = (delimiter, path) => {
  const violations: Violation[] = [];

  if (delimiter === '') {
    violations.push({
      path,
      message: 'Please add a valid delimiter or untick the box if you don’t want to add a delimiter',
    });
  }

  return violations;
};

export {validateDelimiter};
