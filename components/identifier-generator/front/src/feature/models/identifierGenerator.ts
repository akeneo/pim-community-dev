import {LabelCollection} from './labelCollection';
import {IdentifierGeneratorCode} from './identifierGeneratorCode';
import {Target} from './target';
import {Structure} from './structure';
import {Conditions} from './conditions';
import {Delimiter} from './delimiter';

type IdentifierGenerator = {
  code: IdentifierGeneratorCode;
  target: Target;
  structure: Structure;
  conditions: Conditions;
  labels: LabelCollection;
  delimiter: Delimiter | null;
};

export type {IdentifierGenerator};
