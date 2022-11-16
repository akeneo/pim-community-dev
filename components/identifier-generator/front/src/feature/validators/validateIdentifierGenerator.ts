import {IdentifierGenerator} from '../models';
import {Validator} from './Validator';
import {validateIdentifierGeneratorCode} from './validateIdentifierGeneratorCode';
import {validateTarget} from './validateTarget';
import {validateLabelCollection} from './validateLabelCollection';
import {validateStructure} from './validateStructure';
import {validateConditions} from './validateConditions';
import {validateDelimiter} from './validateDelimiter';

const validateIdentifierGenerator: Validator<IdentifierGenerator> = (identifierGenerator, path) => [
  ...validateIdentifierGeneratorCode(identifierGenerator.code, `${path}code`),
  ...validateTarget(identifierGenerator.target, `${path}target`),
  ...validateLabelCollection(identifierGenerator.labels, `${path}labels`),
  ...validateStructure(identifierGenerator.structure, `${path}structure`),
  ...validateConditions(identifierGenerator.conditions, `${path}conditions`),
  ...validateDelimiter(identifierGenerator.delimiter, `${path}delimiter`),
];

export {validateIdentifierGenerator};
