export type MinValue = string | null;
export type NormalizedMinValue = string | null;

export const isValidMinValue = (minValue: NormalizedMinValue): minValue is MinValue => {
  return (typeof minValue === 'string' && !isNaN(Number(minValue))) || isNullMinValue(minValue) || '-' === minValue;
};

export const createMinValueFromNormalized = (minValue: NormalizedMinValue): MinValue => {
  if (!isValidMinValue(minValue)) {
    throw new Error('MinValue should be a string');
  }

  return minValue;
};

export const createMinValueFromString = (minValue: string): MinValue => {
  return createMinValueFromNormalized(minValue === '' ? null : minValue);
};

export const minValueStringValue = (minValue: MinValue): string => {
  return isNullMinValue(minValue) ? '' : minValue;
};

export const isNullMinValue = (minValue: MinValue): minValue is null => null === minValue;
