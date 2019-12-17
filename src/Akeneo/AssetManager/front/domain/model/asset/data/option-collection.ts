import OptionCode, {optionCodesAreEqual} from 'akeneoassetmanager/domain/model/attribute/type/option/option-code';

type OptionCollectionData = OptionCode[] | null;
export type NormalizedOptionCollectionData = string[] | null;

export default OptionCollectionData;
export const isOptionCollectionData = (optionCollectionData: any): optionCollectionData is OptionCollectionData =>
  Array.isArray(optionCollectionData) || optionCollectionData === null;
export const areOptionCollectionDataEqual = (first: OptionCollectionData, second: OptionCollectionData): boolean =>
  (first !== null &&
    second !== null &&
    first.length === second.length &&
    !first.some((optionCode: OptionCode, index: number) => !optionCodesAreEqual(second[index], optionCode))) ||
  (first === null && null === second);

export const optionCollectionArrayValue = (optionCollectionData: OptionCollectionData): OptionCode[] =>
  null === optionCollectionData ? [] : optionCollectionData;
export const optionCollectionFromArray = (optionCollectionData: OptionCode[]): OptionCollectionData =>
  0 === optionCollectionData.length ? null : optionCollectionData;
