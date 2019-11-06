import {InvalidArgumentError} from '../media-link';

export type NormalizedMediaType = string;
export type MediaType = 'image' | 'pdf' | 'youtube' | 'other';

const validMediaTypes = ['image', 'pdf', 'youtube', 'other'];

export enum MediaTypes {
  image = 'image',
  pdf = 'pdf',
  youtube = 'youtube',
  other = 'other',
}

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
