import MediaFileData, {
  NormalizedMediaFileData,
  isMediaFileData,
} from 'akeneoassetmanager/domain/model/asset/data/media-file';
import MediaLinkData, {
  NormalizedMediaLinkData,
  isMediaLinkData,
} from 'akeneoassetmanager/domain/model/asset/data/media-link';
import NumberData, {NormalizedNumberData} from 'akeneoassetmanager/domain/model/asset/data/number';
import OptionCollectionData from 'akeneoassetmanager/domain/model/asset/data/option-collection';
import OptionData from 'akeneoassetmanager/domain/model/asset/data/option';
import TextData, {NormalizedTextData} from 'akeneoassetmanager/domain/model/asset/data/text';
import {NormalizedOptionData} from 'akeneoassetmanager/domain/model/asset/data/option';
import {NormalizedOptionCollectionData} from 'akeneoassetmanager/domain/model/asset/data/option-collection';

export type NormalizedData =
  | NormalizedMediaFileData
  | NormalizedMediaLinkData
  | NormalizedNumberData
  | NormalizedOptionData
  | NormalizedOptionCollectionData
  | NormalizedTextData;

type Data = MediaFileData | MediaLinkData | NumberData | OptionCollectionData | OptionData | TextData;

export const getMediaData = (data: Data): string => {
  if (null === data) return '';
  if (isMediaLinkData(data)) return data;
  if (isMediaFileData(data)) return data.filePath;

  return '';
};

export default Data;
