import {Enabled} from './enabled';

enum CONDITION_NAMES {
  ENABLED = 'enabled',
}

type Condition = {
  type: CONDITION_NAMES;
} & Enabled;

type Conditions = Condition[];

const ALLOWED_CONDITION_NAMES = [CONDITION_NAMES.ENABLED];

export {CONDITION_NAMES, ALLOWED_CONDITION_NAMES};
export type {Conditions, Condition};
