export type AllowedExtensions = string[];

export const isValidAllowedExtension = (allowedExtensions: string[]): allowedExtensions is AllowedExtensions => {
  if (!Array.isArray(allowedExtensions)) {
    return false;
  }

  return allowedExtensions.every((extension: string) => typeof extension === 'string'); // TODO: Leading '.' ?
};

export const createAllowedExtensionFromNormalized = (allowedExtensions: string[]): AllowedExtensions => {
  if (!isValidAllowedExtension(allowedExtensions)) {
    throw new Error(`AllowedExtension is not valid`);
  }

  return allowedExtensions;
};

export const createAllowedExtensionFromArray = (allowedExtensions: string[]): AllowedExtensions => {
  return createAllowedExtensionFromNormalized(allowedExtensions);
};

export const normalizeAllowedExtension = (allowedExtensions: AllowedExtensions): AllowedExtensions => {
  return allowedExtensions;
};
