import AssetFamilyCode, {
  denormalizeCode,
  codesAreEqual,
  codeStringValue,
  isCode,
} from 'akeneoassetmanager/domain/model/code';

export const denormalizeAssetFamilyCode = denormalizeCode;
export const assetcodesAreEqual = codesAreEqual;
export const assetCodeStringValue = codeStringValue;
export const isAssetFamilyCode = isCode;

export default AssetFamilyCode;
