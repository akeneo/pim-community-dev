import {Validator} from './Validator';
import {AutoNumber} from '../models';
import {Violation} from './Violation';

const validateAutoNumber: Validator<AutoNumber> = (autoNumber, path) => {
  const violations: Violation[] = [];

  if (null === autoNumber.digitsMin) {
    violations.push({
      path: `${path}.digitsMin`,
      message: 'You must add a minimum number of digits',
    });
  }

  if (null === autoNumber.numberMin) {
    violations.push({
      path: `${path}.numberMin`,
      message: 'You must add a minimum value',
    });
  }

  return violations;
};

export {validateAutoNumber};
