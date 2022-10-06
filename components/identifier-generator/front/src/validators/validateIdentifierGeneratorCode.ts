import {Validator} from './Validator';
import {Violation} from './Violation';
import {IdentifierGeneratorCode} from '../models';

const validateIdentifierGeneratorCode: Validator<IdentifierGeneratorCode> = (identifierGeneratorCode, path) => {
  const violations: Violation[] = [];

  if (identifierGeneratorCode.trim() === '') {
    violations.push({path, message: 'The identifier generator code must be filled'});
  }

  if (identifierGeneratorCode.length > 100) {
    violations.push({path, message: 'The identifier generator code is too long: it must be 100 characters or less'});
  }

  return violations;
};

export {validateIdentifierGeneratorCode};
