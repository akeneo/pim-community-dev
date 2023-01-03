import {Validator} from './Validator';
import {FreeText} from '../models';
import {Violation} from './Violation';

const validateFreeText: Validator<FreeText> = (freeText, path) => {
  const violations: Violation[] = [];

  if (freeText.string.length === 0) {
    violations.push({
      path: `${path}.string`,
      message: 'The empty values must be filled',
    });
  }

  if (/[ ,;]/.exec(freeText.string)) {
    violations.push({
      path,
      message: 'The property must not contain a comma, a semicolon or any space',
    });
  }

  return violations;
};

export {validateFreeText};
