import {Validator} from './Validator';
import {IdentifierGeneratorCode} from '../models';
import {ValidationError} from './ValidationError';

const validateIdentifierGeneratorCode: Validator<IdentifierGeneratorCode> = (identifierGeneratorCode, path) => {
  const result: ValidationError[] = [];

  if (identifierGeneratorCode.trim() === '') {
    result.push({path, message: 'Identifier generator code should not be empty'});
  }

  if (identifierGeneratorCode.length >= 100) {
    result.push({path, message: 'Identifier generator code max length is 100'});
  }

  return result;
};

export {validateIdentifierGeneratorCode};
