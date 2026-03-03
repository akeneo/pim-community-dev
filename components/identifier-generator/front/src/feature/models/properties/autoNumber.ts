import {PROPERTY_NAMES} from '../structure';

type AutoNumber = {
  type: PROPERTY_NAMES.AUTO_NUMBER;
  digitsMin: number | null;
  numberMin: number | null;
};

export type {AutoNumber};
