import {Validator} from './Validator';
import {Delimiter} from '../models';
import {Violation} from './Violation';

const validateDelimiter: Validator<Delimiter | null> = (delimiter, path) => {
  const violations: Violation[] = [];

  if (delimiter === '') {
    violations.push({
      path,
      message: 'A valid delimiter must be added. If you do not want to have a delimiter, untick the box.',
    });
  }

  if (delimiter !== null && /[ ,;]/.exec(delimiter)) {
    violations.push({
      path,
      message: 'The property must not contain a comma, a semicolon or any space',
    });
  }
  return violations;
};

export {validateDelimiter};
