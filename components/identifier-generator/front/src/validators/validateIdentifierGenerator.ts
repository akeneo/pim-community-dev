import {IdentifierGenerator} from '../models';
import {Validator} from './Validator';
import {validateIdentifierGeneratorCode} from './validateIdentifierGeneratorCode';
import {validateTarget} from './validateTarget';
import {validateLabelCollection} from './validateLabelCollection';
import {validateStructure} from './validateStructure';
import {validateConditions} from './validateConditions';
import {validateDelimiter} from './validateDelimiter';

const validateIdentifierGenerator: Validator<IdentifierGenerator> = (identifierGenerator, _path) => [
  ...validateIdentifierGeneratorCode(identifierGenerator.code, 'code'),
  ...validateTarget(identifierGenerator.target, 'target'),
  ...validateLabelCollection(identifierGenerator.labels, 'labels'),
  ...validateStructure(identifierGenerator.structure, 'structure'),
  ...validateConditions(identifierGenerator.conditions, 'conditions'),
  ...validateDelimiter(identifierGenerator.delimiter, 'delimiter'),
];

export {validateIdentifierGenerator};
