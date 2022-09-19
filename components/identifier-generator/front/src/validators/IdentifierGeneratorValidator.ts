import {IdentifierGenerator} from '../models';
import {Validator} from './Validator';
import {validateIdentifierGeneratorCode} from './IdentifierGeneratorCodeValidator';
import {validateTarget} from './TargetValidator';
import {validateLabelCollection} from './LabelCollectionValidator';
import {validateStructure} from './StructureValidator';
import {validateConditions} from './ConditionsValidator';
import {validateDelimiter} from './DelimiterValidator';

const validateIdentifierGenerator: Validator<IdentifierGenerator> = (identifierGenerator, _path) => [
  ...validateIdentifierGeneratorCode(identifierGenerator.code, 'code'),
  ...validateTarget(identifierGenerator.target, 'target'),
  ...validateLabelCollection(identifierGenerator.labels, 'labels'),
  ...validateStructure(identifierGenerator.structure, 'structure'),
  ...validateConditions(identifierGenerator.conditions, 'conditions'),
  ...validateDelimiter(identifierGenerator.delimiter, 'delimiter'),
];

export {validateIdentifierGenerator};
