export type NormalizedMediaType = string;
export type MediaType = 'image' | 'pdf' | 'youtube' | 'other';

export const YOUTUBE_WATCH_URL = 'https://youtube.com/watch?v=';
export const YOUTUBE_EMBED_URL = 'https://youtube.com/embed/';

export const YOUTUBE_WATCH_URL = 'https://youtube.com/watch?v=';
export const YOUTUBE_EMBED_URL = 'https://youtube.com/embed/';

export enum MediaTypes {
  image = 'image',
  pdf = 'pdf',
  youtube = 'youtube',
  other = 'other',
}

const validMediaTypes = [MediaTypes.image, MediaTypes.pdf, MediaTypes.youtube, MediaTypes.other];

export const isValidMediaType = (mediaType: NormalizedMediaType): mediaType is MediaType => {
  return validMediaTypes.includes(mediaType as MediaTypes);
};

export const createMediaTypeFromNormalized = (mediaType: NormalizedMediaType): MediaType => {
  if (!isValidMediaType(mediaType)) {
    throw new Error(`MediaType should be ${validMediaTypes.join(',')}`);
  }

  return mediaType;
};

export const createMediaTypeFromString = (mediaType: string): MediaType => {
  return createMediaTypeFromNormalized(mediaType);
};

export const normalizeMediaType = (mediaType: MediaType): NormalizedMediaType => {
  return mediaType;
};
