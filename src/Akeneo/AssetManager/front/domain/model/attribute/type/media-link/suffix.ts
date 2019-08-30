import {InvalidArgumentError} from '../media-link';

export type Suffix = string | null;
export type NormalizedSuffix = string | null;

export const isValidSuffix = (suffix: NormalizedSuffix): suffix is Suffix => {
  return typeof suffix === 'string' || null === suffix;
};

export const createEmptySuffix = (): Suffix => {
  return null;
};

export const createSuffixFromNormalized = (suffix: any): Suffix => {
  if (!isValidSuffix(suffix)) {
    throw new InvalidArgumentError('Suffix should be a string');
  }

  return suffix;
};

export const createSuffixFromString = (suffix: string): Suffix => {
  return createSuffixFromNormalized(suffix === '' ? null : suffix);
};

export const normalizeSuffix = (suffix: Suffix): NormalizedSuffix => {
  return suffix;
};

export const suffixStringValue = (suffix: Suffix): string => {
  return null === suffix ? '' : suffix;
};
