import {CONDITION_NAMES} from './conditions';
import {OptionCode} from '../option';

type SimpleSelectOptionCondition = {
  type: CONDITION_NAMES.SELECT_OPTION;
  value: OptionCode[] | null;
};

export type {SimpleSelectOptionCondition};
