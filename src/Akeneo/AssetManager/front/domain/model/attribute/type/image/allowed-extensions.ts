export type AllowedExtensions = AllowedExtensionsOptions[];

export enum AllowedExtensionsOptions {
  gif = 'gif',
  jfif = 'jfif',
  jif = 'jif',
  jpeg = 'jpeg',
  jpg = 'jpg',
  pdf = 'pdf',
  png = 'png',
  psd = 'psd',
  tif = 'tif',
  tiff = 'tiff',
}

export const validAllowedExtensionsOptions = Object.values(AllowedExtensionsOptions);

export const isValidAllowedExtension = (allowedExtensions: string[]): allowedExtensions is AllowedExtensions => {
  if (!Array.isArray(allowedExtensions)) {
    return false;
  }
  return !allowedExtensions.some((extension: string) => !Object.values(AllowedExtensionsOptions).includes(extension));
};

export const createAllowedExtensionFromNormalized = (allowedExtensions: string[]): AllowedExtensions => {
  if (!isValidAllowedExtension(allowedExtensions)) {
    throw new Error(`AllowedExtension should be ${validAllowedExtensionsOptions.join(',')}`);
  }

  return allowedExtensions;
};

export const createAllowedExtensionFromArray = (allowedExtensions: string[]): AllowedExtensions => {
  return createAllowedExtensionFromNormalized(allowedExtensions);
};

export const normalizeAllowedExtension = (allowedExtensions: AllowedExtensions): AllowedExtensions => {
  return allowedExtensions;
};
