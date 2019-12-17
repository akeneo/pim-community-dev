import Data from 'akeneoassetmanager/domain/model/asset/data';

type NumberData = string | null;

export default NumberData;
export type NormalizedNumberData = string | null;

export const isNumberData = (numberData: any): numberData is NumberData =>
  typeof numberData === 'string' || null === numberData;
export const areNumberDataEqual = (first: Data, second: Data): boolean =>
  isNumberData(first) && isNumberData(second) && first === second;
export const numberDataStringValue = (numberData: NumberData) => (null === numberData ? '' : numberData);
export const numberDataFromString = (numberData: string): NumberData => (0 === numberData.length ? null : numberData);
