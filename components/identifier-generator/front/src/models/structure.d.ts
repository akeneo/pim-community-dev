import {AutoNumber, FreeText} from './properties';

enum PROPERTY_NAMES {
  AUTO_NUMBER = 'auto_number',
  FREE_TEXT = 'free_text',
}

type Property = {propertyName: PROPERTY_NAMES} & (AutoNumber | FreeText);

type Structure = Property[];

export {PROPERTY_NAMES};
export type {Structure};
