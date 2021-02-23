type TextData = string | null;
type NormalizedTextData = string | null;

const isTextData = (textData: any): textData is TextData => typeof textData === 'string' || null === textData;
const textDataStringValue = (textData: TextData) => (null === textData ? '' : textData);
const textDataFromString = (textData: string): TextData =>
  0 === textData.length || '<p></p>\n' === textData ? null : textData;

export {isTextData, textDataStringValue, textDataFromString, TextData, NormalizedTextData};
export default TextData;
