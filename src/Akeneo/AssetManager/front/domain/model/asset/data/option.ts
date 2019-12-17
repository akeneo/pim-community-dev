import OptionCode from 'akeneoassetmanager/domain/model/attribute/type/option/option-code';

type OptionData = OptionCode | null;
export type NormalizedOptionData = string | null;

export default OptionData;

export const isOptionData = (optionData: any): optionData is OptionData =>
  typeof optionData === 'string' || null === optionData;
export const areOptionDataEqual = (first: OptionData, second: OptionData): boolean =>
  isOptionData(first) && isOptionData(second) && first === second;
export const optionDataStringValue = (optionData: OptionData) => (null === optionData ? '' : optionData);
export const optionDataFromString = (optionData: string): OptionData => (0 === optionData.length ? null : optionData);
