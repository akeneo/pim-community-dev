import Data from 'akeneoassetmanager/domain/model/asset/data';

type MediaLinkData = string | null;
export type NormalizedMediaLinkData = string | null;

export default MediaLinkData;
export const isMediaLinkData = (mediaLinkData: any): mediaLinkData is MediaLinkData =>
  typeof mediaLinkData === 'string' || null === mediaLinkData;
export const areMediaLinkDataEqual = (first: Data, second: Data): boolean =>
  isMediaLinkData(first) && isMediaLinkData(second) && first === second;
export const mediaLinkDataStringValue = (mediaLinkData: MediaLinkData) => (null === mediaLinkData ? '' : mediaLinkData);
export const mediaLinkDataFromString = (mediaLinkData: string): MediaLinkData =>
  0 === mediaLinkData.length ? null : mediaLinkData;
