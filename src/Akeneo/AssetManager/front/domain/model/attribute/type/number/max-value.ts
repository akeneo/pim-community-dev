export type MaxValue = string | null;
export type NormalizedMaxValue = string | null;

export const isValidMaxValue = (maxValue: NormalizedMaxValue): maxValue is MaxValue => {
  return (typeof maxValue === 'string' && !isNaN(Number(maxValue))) || isNullMaxValue(maxValue) || '-' === maxValue;
};

export const createMaxValueFromNormalized = (maxValue: NormalizedMaxValue): MaxValue => {
  if (!isValidMaxValue(maxValue)) {
    throw new Error('MaxValue should be a string');
  }

  return maxValue;
};

export const createMaxValueFromString = (maxValue: string): MaxValue => {
  return createMaxValueFromNormalized(maxValue === '' ? null : maxValue);
};

export const maxValueStringValue = (maxValue: MaxValue): string => {
  return isNullMaxValue(maxValue) ? '' : maxValue;
};

export const isNullMaxValue = (maxValue: MaxValue): maxValue is null => null === maxValue;
