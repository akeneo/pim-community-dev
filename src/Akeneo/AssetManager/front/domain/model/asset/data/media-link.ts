import Data from 'akeneoassetmanager/domain/model/asset/data';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import {isMediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';

type MediaLinkData = string | null;
export type NormalizedMediaLinkData = string | null;

export const isMediaLinkData = (mediaLinkData: any): mediaLinkData is MediaLinkData =>
  typeof mediaLinkData === 'string' || null === mediaLinkData;
export const areMediaLinkDataEqual = (first: Data, second: Data): boolean =>
  isMediaLinkData(first) && isMediaLinkData(second) && first === second;
export const mediaLinkDataStringValue = (mediaLinkData: MediaLinkData) => (null === mediaLinkData ? '' : mediaLinkData);
export const mediaLinkDataFromString = (mediaLinkData: string): MediaLinkData =>
  0 === mediaLinkData.length ? null : mediaLinkData;
export const getMediaLinkValueUrl = (editionValue: EditionValue): string => {
  if (!isMediaLinkAttribute(editionValue.attribute)) {
    throw Error('EditionValue should be a MediaLinkValue');
  }

  return `${editionValue.attribute.prefix}${editionValue.data}${editionValue.attribute.suffix}`;
};

export default MediaLinkData;
