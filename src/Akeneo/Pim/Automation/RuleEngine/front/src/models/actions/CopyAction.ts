import { CopyActionLine } from '../../pages/EditRules/components/actions/CopyActionLine';
import { ActionModuleGuesser } from './ActionModuleGuesser';
import { AttributeType } from "../Attribute";

export const supportedTypes: Partial<Record<AttributeType, AttributeType[]>> = {
  [AttributeType.OPTION_SIMPLE_SELECT]: [
    AttributeType.OPTION_SIMPLE_SELECT,
    AttributeType.REFERENCE_ENTITY_SIMPLE_SELECT,
    AttributeType.TEXT,
    AttributeType.TEXTAREA,
  ],
  [AttributeType.OPTION_MULTI_SELECT]: [
    AttributeType.OPTION_MULTI_SELECT,
    AttributeType.REFERENCE_ENTITY_COLLECTION,
    AttributeType.TEXT,
    AttributeType.TEXTAREA,
  ],
  [AttributeType.TEXT]: [
    AttributeType.TEXT,
    AttributeType.TEXTAREA,
    AttributeType.OPTION_SIMPLE_SELECT,
    AttributeType.REFERENCE_ENTITY_SIMPLE_SELECT,
  ],
  [AttributeType.IDENTIFIER]: [
    AttributeType.IDENTIFIER,
    AttributeType.TEXT,
    AttributeType.TEXTAREA,
  ],
  [AttributeType.DATE]: [
    AttributeType.DATE,
    AttributeType.TEXT,
    AttributeType.TEXTAREA,
  ],
  [AttributeType.METRIC]: [
    AttributeType.METRIC,
    AttributeType.TEXT,
    AttributeType.TEXTAREA,
    AttributeType.NUMBER,
  ],
  [AttributeType.NUMBER]: [
    AttributeType.NUMBER,
    AttributeType.TEXT,
    AttributeType.TEXTAREA,
    AttributeType.METRIC,
  ],
  [AttributeType.PRICE_COLLECTION]: [
    AttributeType.PRICE_COLLECTION,
    AttributeType.TEXT,
    AttributeType.TEXTAREA,
  ],
  [AttributeType.REFERENCE_ENTITY_SIMPLE_SELECT]: [
    AttributeType.REFERENCE_ENTITY_SIMPLE_SELECT,
    AttributeType.TEXT,
    AttributeType.TEXTAREA,
    AttributeType.OPTION_SIMPLE_SELECT,
  ],
  [AttributeType.REFERENCE_ENTITY_COLLECTION]: [
    AttributeType.REFERENCE_ENTITY_COLLECTION,
    AttributeType.TEXT,
    AttributeType.TEXTAREA,
  ]
}

export type CopyAction = {
  type: 'copy';
  from_field: string;
  from_locale: string | null;
  from_scope: string | null;
  to_field: string;
  to_locale: string | null;
  to_scope: string | null;
};

export const getCopyActionModule: ActionModuleGuesser = json => {
  if (json.type !== 'copy') {
    return Promise.resolve(null);
  }

  return Promise.resolve(CopyActionLine);
};
