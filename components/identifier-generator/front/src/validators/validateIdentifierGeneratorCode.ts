import {Validator} from './Validator';
import {Violation} from './Violation';
import {IdentifierGeneratorCode} from '../models';

const validateIdentifierGeneratorCode: Validator<IdentifierGeneratorCode> = (identifierGeneratorCode, path) => {
  const violations: Violation[] = [];

  if (identifierGeneratorCode.trim() === '') {
    violations.push({path, message: 'Identifier generator code should not be empty'});
  }

  if (identifierGeneratorCode.length >= 100) {
    violations.push({path, message: 'Identifier generator code max length is 100'});
  }

  return violations;
};

export {validateIdentifierGeneratorCode};
