import {Validator} from './Validator';
import {Conditions} from '../models';

// eslint-disable-next-line @typescript-eslint/no-unused-vars
const validateConditions: Validator<Conditions | undefined> = (_conditions, _path) => {
  return [];
};

export {validateConditions};
