export type MaxFileSize = string | null;

export const isValidMaxFileSize = (maxFileSize: string | null): maxFileSize is MaxFileSize => {
  return (
    isNullMaxFileSize(maxFileSize) ||
    (typeof maxFileSize === 'string' && maxFileSize.length > 0 && null !== maxFileSize.match(/^[0-9]*\.?[0-9]*$/))
  );
};

export const createMaxFileSizeFromNormalized = (maxFileSize: string | null): MaxFileSize => {
  if (!isValidMaxFileSize(maxFileSize)) {
    throw new Error('MaxFileSize should be a string');
  }

  return maxFileSize;
};

export const createMaxFileSizeFromString = (maxFileSize: string): MaxFileSize => {
  return createMaxFileSizeFromNormalized(maxFileSize === '' ? null : maxFileSize);
};

export const maxFileSizeStringValue = (maxFileSize: MaxFileSize): string => {
  return isNullMaxFileSize(maxFileSize) ? '' : maxFileSize;
};

export const isNullMaxFileSize = (maxFileSize: MaxFileSize): maxFileSize is null => null === maxFileSize;
