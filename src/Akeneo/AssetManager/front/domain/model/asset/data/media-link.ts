import Data from 'akeneoassetmanager/domain/model/asset/data';
import {
  isMediaLinkAttribute,
  NormalizedMediaLinkAttribute,
} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {suffixStringValue} from 'akeneoassetmanager/domain/model/attribute/type/media-link/suffix';
import {prefixStringValue} from 'akeneoassetmanager/domain/model/attribute/type/media-link/prefix';
import {MediaTypes} from 'akeneoassetmanager/domain/model/attribute/type/media-link/media-type';

const YOUTUBE_WATCH_URL = 'https://youtube.com/watch?v=';
const YOUTUBE_EMBED_URL = 'https://youtube.com/embed/';

const VIMEO_WATCH_URL = 'https://vimeo.com/';
const VIMEO_EMBED_URL = 'https://player.vimeo.com/video/';

type MediaLinkData = string | null;
export type NormalizedMediaLinkData = string | null;

export const isMediaLinkData = (mediaLinkData: any): mediaLinkData is MediaLinkData =>
  typeof mediaLinkData === 'string' || null === mediaLinkData;

export const areMediaLinkDataEqual = (first: Data, second: Data): boolean =>
  isMediaLinkData(first) && isMediaLinkData(second) && first === second;

export const mediaLinkDataStringValue = (mediaLinkData: MediaLinkData): string =>
  null === mediaLinkData ? '' : mediaLinkData;

export const mediaLinkDataFromString = (mediaLinkData: string): MediaLinkData =>
  0 === mediaLinkData.length ? null : mediaLinkData;

export const getMediaLinkUrl = (data: MediaLinkData, attribute: NormalizedAttribute): string => {
  if (!isMediaLinkAttribute(attribute)) {
    throw Error('EditionValue should be a MediaLinkValue');
  }

  switch (attribute.media_type) {
    case MediaTypes.youtube:
      return getYouTubeWatchUrl(data);
    case MediaTypes.vimeo:
      return getVimeoWatchUrl(data);
    default:
      return `${prefixStringValue(attribute.prefix)}${mediaLinkDataStringValue(data)}${suffixStringValue(
        attribute.suffix
      )}`;
  }
};

export const canDownloadMediaLink = (attribute: NormalizedMediaLinkAttribute): boolean =>
  MediaTypes.youtube !== attribute.media_type && MediaTypes.vimeo !== attribute.media_type;

export const getYouTubeWatchUrl = (data: MediaLinkData): string =>
  `${YOUTUBE_WATCH_URL}${mediaLinkDataStringValue(data)}`;

export const getYouTubeEmbedUrl = (data: MediaLinkData): string =>
  `${YOUTUBE_EMBED_URL}${mediaLinkDataStringValue(data)}`;

export const getVimeoWatchUrl = (data: MediaLinkData): string => `${VIMEO_WATCH_URL}${mediaLinkDataStringValue(data)}`;

export const getVimeoEmbedUrl = (data: MediaLinkData): string => `${VIMEO_EMBED_URL}${mediaLinkDataStringValue(data)}`;

export default MediaLinkData;
