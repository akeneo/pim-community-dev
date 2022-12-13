import {CONDITION_NAMES} from './conditions';

type Enabled = {
  type: CONDITION_NAMES.ENABLED;
  value: boolean;
};

export type {Enabled};
