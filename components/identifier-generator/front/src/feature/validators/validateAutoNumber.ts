import {Validator} from './Validator';
import {AutoNumber} from '../models';
import {Violation} from './Violation';

const validateAutoNumber: Validator<AutoNumber> = (autoNumber, path) => {
  const violations: Violation[] = [];

  if (null === autoNumber.digitsMin) {
    violations.push({
      path: `${path}.digitsMin`,
      message: 'DigitsMin should not be empty',
    });
  }

  if (null === autoNumber.numberMin) {
    violations.push({
      path: `${path}.numberMin`,
      message: 'NumberMin should not be empty',
    });
  }

  return violations;
};

export {validateAutoNumber};
