import {Validator} from './Validator';
import {Structure} from '../models';
import {Violation} from './Violation';

const validateStructure: Validator<Structure> = (structure, path) => {
  const violations: Violation[] = [];

  if (structure.length === 0) {
    violations.push({
      path,
      message: 'The structure must contain at least 1 property',
    });
  }

  return violations;
};

export {validateStructure};
