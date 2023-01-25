import {CONDITION_NAMES} from './conditions';
import {AttributeCode} from '../attribute';

type IdentifierCondition = {
  type: CONDITION_NAMES.IDENTIFIER;
  operator: 'EMPTY';

  attributeCode: AttributeCode;
  auto: boolean;
}

export type {IdentifierCondition};
