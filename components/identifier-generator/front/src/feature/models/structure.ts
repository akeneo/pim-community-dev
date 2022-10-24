import {AutoNumber, FreeText} from './properties';

enum PROPERTY_NAMES {
  AUTO_NUMBER = 'auto_number',
  FREE_TEXT = 'free_text',
}

const ALLOWED_PROPERTY_NAMES = [PROPERTY_NAMES.FREE_TEXT, PROPERTY_NAMES.AUTO_NUMBER];

type Property = {type: PROPERTY_NAMES} & (AutoNumber | FreeText);

type Structure = Property[];

export {ALLOWED_PROPERTY_NAMES, PROPERTY_NAMES};
export type {Structure};
