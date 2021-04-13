export type NormalizedMediaType = string;
export type MediaType = MediaTypes.image | MediaTypes.other | MediaTypes.pdf;

export enum MediaTypes {
  image = 'image',
  pdf = 'pdf',
  other = 'other',
}

const validMediaTypes = Object.values(MediaTypes);

export const isValidMediaType = (mediaType: NormalizedMediaType): mediaType is MediaType =>
  validMediaTypes.includes(mediaType as MediaTypes);

export const createMediaTypeFromNormalized = (mediaType: NormalizedMediaType): MediaType => {
  if (!isValidMediaType(mediaType)) {
    throw new Error(`MediaType should be ${validMediaTypes.join(',')}`);
  }

  return mediaType;
};

export const createMediaTypeFromString = (mediaType: string): MediaType => createMediaTypeFromNormalized(mediaType);
