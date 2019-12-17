import Data from 'akeneoassetmanager/domain/model/asset/data';

export type TextData = string | null;
export type NormalizedTextData = string | null;

export default TextData;
export const isTextData = (textData: any): textData is TextData => typeof textData === 'string' || null === textData;
export const areTextDataEqual = (first: Data, second: Data): boolean =>
  isTextData(first) && isTextData(second) && first === second;
export const textDataStringValue = (textData: TextData) => (null === textData ? '' : textData);
export const textDataFromString = (textData: string): TextData =>
  0 === textData.length || '<p></p>\n' === textData ? null : textData;
