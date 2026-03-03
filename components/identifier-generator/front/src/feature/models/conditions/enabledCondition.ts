import {CONDITION_NAMES} from './conditions';

type EnabledCondition = {
  type: CONDITION_NAMES.ENABLED;
  value?: boolean;
};

export type {EnabledCondition};
