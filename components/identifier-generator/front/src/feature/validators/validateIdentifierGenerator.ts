import {IdentifierGenerator} from '../models';
import {
  validateConditions,
  validateDelimiter,
  validateIdentifierGeneratorCode,
  validateLabelCollection,
  validateStructure,
  validateTarget,
  Validator
} from './';

const validateIdentifierGenerator: Validator<IdentifierGenerator> = (identifierGenerator, path) => [
  ...validateIdentifierGeneratorCode(identifierGenerator.code, `${path}code`),
  ...validateTarget(identifierGenerator.target, `${path}target`),
  ...validateLabelCollection(identifierGenerator.labels, `${path}labels`),
  ...validateStructure(identifierGenerator.structure, `${path}structure`),
  ...validateConditions(identifierGenerator.conditions, `${path}conditions`),
  ...validateDelimiter(identifierGenerator.delimiter, `${path}delimiter`),
];

export {validateIdentifierGenerator};
