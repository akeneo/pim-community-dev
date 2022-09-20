import {Validator} from './Validator';
import {Conditions} from '../models/conditions';

const validateConditions: Validator<Conditions> = (_conditions, _path) => {
  return [];
};

export {validateConditions};
