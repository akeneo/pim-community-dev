import {InvalidArgumentError} from '../media-link';

export type Prefix = string | null;
export type NormalizedPrefix = string | null;

export const isValidPrefix = (prefix: NormalizedPrefix): prefix is Prefix => {
  return typeof prefix === 'string' || null === prefix;
};

export const createEmptyPrefix = (): Prefix => {
  return null;
};

export const createPrefixFromNormalized = (prefix: NormalizedPrefix): Prefix => {
  if (!isValidPrefix(prefix)) {
    throw new InvalidArgumentError('Prefix should be a string');
  }

  return prefix;
};

export const createPrefixFromString = (prefix: string): Prefix => {
  return createPrefixFromNormalized(prefix === '' ? null : prefix);
};

export const normalizePrefix = (prefix: Prefix): NormalizedPrefix => {
  return prefix;
};

export const prefixStringValue = (prefix: Prefix): string => {
  return null === prefix ? '' : prefix;
};
