import {Validator} from './Validator';
import {Conditions} from '../models';

const validateConditions: Validator<Conditions | undefined> = (_conditions, _path) => {
  return [];
};

export {validateConditions};
