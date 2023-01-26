import {AutoNumber, FamilyCodeProperty, FreeText} from './properties';

enum PROPERTY_NAMES {
  AUTO_NUMBER = 'auto_number',
  FREE_TEXT = 'free_text',
  FAMILY_CODE = 'family_code'
}

const ALLOWED_PROPERTY_NAMES = [
  PROPERTY_NAMES.FREE_TEXT,
  PROPERTY_NAMES.AUTO_NUMBER,
  PROPERTY_NAMES.FAMILY_CODE,
];

type Property = {type: PROPERTY_NAMES} & (AutoNumber | FreeText | FamilyCodeProperty);

type Structure = Property[];

export {ALLOWED_PROPERTY_NAMES, PROPERTY_NAMES};
export type {Structure, Property};
