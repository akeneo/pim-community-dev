import {Enabled} from './enabled';

type Condition = {
  type: CONDITION_NAMES;
} & Enabled;

type Conditions = Condition[];

enum CONDITION_NAMES {
  ENABLED = 'enabled',
}

export {CONDITION_NAMES};
export type {Conditions, Condition};
