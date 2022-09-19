import {IdentifierGeneratorCode} from './IdentifierGeneratorCode';
import {Structure} from './Structure';
import {Conditions} from './Conditions';
import {Delimiter} from './Delimiter';
import {Target} from './Target';
import {LabelCollection} from './LabelCollection';

type IdentifierGenerator = {
  code: IdentifierGeneratorCode;
  target: Target;
  structure: Structure;
  conditions: Conditions;
  labels: LabelCollection;
  delimiter?: Delimiter;
};

export type {IdentifierGenerator};
