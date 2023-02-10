import {IdentifierGenerator} from '../models';
import {
  validateConditions,
  validateDelimiter,
  validateIdentifierGeneratorCode,
  validateLabelCollection,
  validateStructure,
  validateTarget,
  Violation,
} from './';

const validateIdentifierGenerator = (identifierGenerator: IdentifierGenerator): Violation[] => [
  ...validateIdentifierGeneratorCode(identifierGenerator.code, 'code'),
  ...validateTarget(identifierGenerator.target, 'target'),
  ...validateLabelCollection(identifierGenerator.labels, 'labels'),
  ...validateStructure(identifierGenerator.structure, 'structure'),
  ...validateConditions(identifierGenerator.conditions, 'conditions'),
  ...validateDelimiter(identifierGenerator.delimiter, 'delimiter'),
];

export {validateIdentifierGenerator};
