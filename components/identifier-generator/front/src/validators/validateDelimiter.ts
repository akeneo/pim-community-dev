import {Validator} from './Validator';
import {Delimiter} from '../models';

const validateDelimiter: Validator<Delimiter | undefined> = (_delimiter, _path) => {
  return [];
};

export {validateDelimiter};
