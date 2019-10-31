import AttributeCode, {
  denormalizeCode,
  codesAreEqual,
  codeStringValue,
  isCode,
} from 'akeneoassetmanager/domain/model/code';

export const denormalizeAttributeCode = denormalizeCode;
export const attributecodesAreEqual = codesAreEqual;
export const attributeCodeStringValue = codeStringValue;
export const isAttributeCode = isCode;

export default AttributeCode;
