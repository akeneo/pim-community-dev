import OptionCode, {
  denormalizeCode,
  codesAreEqual,
  codeStringValue,
  isCode,
} from 'akeneoassetmanager/domain/model/code';

export const denormalizeOptionCode = denormalizeCode;
export const optioncodesAreEqual = codesAreEqual;
export const optionCodeStringValue = codeStringValue;
export const isOptionCode = isCode;

export default OptionCode;
