import {Validator} from './Validator';
import {Target} from '../models';
import {ValidationError} from './ValidationError';

const validateTarget: Validator<Target> = (target, path) => {
  const result: ValidationError[] = [];

  if (target.trim() === '') {
    result.push({path, message: 'Target should not be empty'});
  }

  return result;
};

export {validateTarget};
