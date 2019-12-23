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
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {isMediaFileAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {isMediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';

export type NormalizedData =
  | NormalizedMediaFileData
  | NormalizedMediaLinkData
  | NormalizedNumberData
  | NormalizedOptionData
  | NormalizedOptionCollectionData
  | NormalizedTextData;

type Data = MediaFileData | MediaLinkData | NumberData | OptionCollectionData | OptionData | TextData;

export const getMediaData = (data: Data, attribute: NormalizedAttribute): string => {
  if (null === data) return '';
  if (isMediaLinkAttribute(attribute) && isMediaLinkData(data)) return data;
  if (isMediaFileAttribute(attribute) && isMediaFileData(data)) return data.filePath;

  return '';
};

export default Data;
