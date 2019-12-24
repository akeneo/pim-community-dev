import Data from 'akeneoassetmanager/domain/model/asset/data';
import {isMediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';

const YOUTUBE_WATCH_URL = 'https://youtube.com/watch?v=';
const YOUTUBE_EMBED_URL = 'https://youtube.com/embed/';

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

  return `${attribute.prefix}${data}${attribute.suffix}`;
};

export const getYouTubeWatchUrl = (data: MediaLinkData): string =>
  `${YOUTUBE_WATCH_URL}${mediaLinkDataStringValue(data)}`;

export const getYouTubeEmbedUrl = (data: MediaLinkData): string =>
  `${YOUTUBE_EMBED_URL}${mediaLinkDataStringValue(data)}`;

export default MediaLinkData;
