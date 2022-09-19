import {Validator} from './Validator';
import {Structure} from '../models';
import {ValidationError} from './ValidationError';

const validateStructure: Validator<Structure> = (structure, path) => {
  const result: ValidationError[] = [];

  if (structure.length === 0) {
    result.push({
      path,
      message: 'You need at least one property',
    });
  }

  return result;
};

export {validateStructure};
