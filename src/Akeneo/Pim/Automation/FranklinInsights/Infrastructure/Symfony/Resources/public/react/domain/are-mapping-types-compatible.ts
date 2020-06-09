import {FranklinAttributeType} from './model/franklin-attribute-type.enum';
import {AttributeType} from './model/attribute-type.enum';

const PERFECT_MAPPINGS: {
  [attributeType: string]: string[];
} = {
  [FranklinAttributeType.METRIC]: [AttributeType.TEXT, AttributeType.TEXTAREA, AttributeType.METRIC],
  [FranklinAttributeType.SELECT]: [
    AttributeType.TEXT,
    AttributeType.TEXTAREA,
    AttributeType.SIMPLESELECT,
    AttributeType.MULTISELECT
  ],
  [FranklinAttributeType.MULTISELECT]: [
    AttributeType.TEXT,
    AttributeType.TEXTAREA,
    AttributeType.MULTISELECT,
    AttributeType.SIMPLESELECT
  ],
  [FranklinAttributeType.NUMBER]: [AttributeType.TEXT, AttributeType.TEXTAREA, AttributeType.NUMBER],
  [FranklinAttributeType.TEXT]: [AttributeType.TEXT, AttributeType.TEXTAREA],
  [FranklinAttributeType.BOOLEAN]: [AttributeType.BOOLEAN]
};

export function areMappingTypesCompatible(
  franklinAttributeType: FranklinAttributeType,
  pimAttributeType: AttributeType | null
): boolean {
  if (pimAttributeType === null) {
    return true;
  }

  return PERFECT_MAPPINGS[franklinAttributeType].includes(pimAttributeType);
}
