import {Validator} from './Validator';
import {FreeText} from '../models';
import {Violation} from './Violation';

const validateFreeText: Validator<FreeText> = (freeText, path) => {
  const violations: Violation[] = [];

  if (freeText.string.length === 0) {
    violations.push({
      path: `${path}.string`,
      message: 'Please fill in the text area to save this property',
    });
  }

  return violations;
};

export {validateFreeText};
