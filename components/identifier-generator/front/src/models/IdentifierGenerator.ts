import {IdentifierGeneratorCode} from './IdentifierGeneratorCode';
import {Structure} from './Structure';
import {Conditions} from './Conditions';
import {LabelCollection} from '@akeneo-pim-community/shared';
import {Delimiter} from './Delimiter';
import {Target} from './Target';

type IdentifierGenerator = {
  code: IdentifierGeneratorCode;
  target: Target;
  structure: Structure;
  conditions: Conditions;
  labelCollection: LabelCollection;
  delimiter?: Delimiter;
};

export type {IdentifierGenerator};
