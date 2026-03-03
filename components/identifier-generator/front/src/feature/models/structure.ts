import {AutoNumber, FamilyProperty, FreeText, RefEntityProperty, SimpleSelectProperty} from './properties';

enum PROPERTY_NAMES {
  AUTO_NUMBER = 'auto_number',
  FREE_TEXT = 'free_text',
  FAMILY = 'family',
  SIMPLE_SELECT = 'simple_select',
  REF_ENTITY = 'reference_entity',
}

const ALLOWED_PROPERTY_NAMES = [
  PROPERTY_NAMES.FREE_TEXT,
  PROPERTY_NAMES.AUTO_NUMBER,
  PROPERTY_NAMES.FAMILY,
  PROPERTY_NAMES.SIMPLE_SELECT,
  PROPERTY_NAMES.REF_ENTITY,
];

type Property = {type: PROPERTY_NAMES} & (
  | AutoNumber
  | FreeText
  | FamilyProperty
  | SimpleSelectProperty
  | RefEntityProperty
);

type Structure = Property[];

export {ALLOWED_PROPERTY_NAMES, PROPERTY_NAMES};
export type {Structure, Property};
