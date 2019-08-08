import {InvalidArgumentError} from '../media-link';

export type NormalizedMediaType = string;
export type MediaType = 'image' | 'other';

const validMediaTypes = ['image', 'other'];

export const isValidMediaType = (mediaType: NormalizedMediaType): mediaType is MediaType => {
  return validMediaTypes.includes(mediaType);
};

export const createMediaTypeFromNormalized = (mediaType: NormalizedMediaType): MediaType => {
  if (!isValidMediaType(mediaType)) {
    throw new InvalidArgumentError(`MediaType should be ${validMediaTypes.join(',')}`);
  }

  return mediaType;
};

export const createMediaTypeFromString = (mediaType: string): MediaType => {
  return createMediaTypeFromNormalized(mediaType);
};

export const normalizeMediaType = (mediaType: MediaType): NormalizedMediaType => {
  return mediaType;
};
