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
import ListValue from 'akeneoassetmanager/domain/model/asset/list-value';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
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

//TODO where to put this?
export const getValueData = (value: ListValue | EditionValue, attribute: NormalizedAttribute): string => {
  if (undefined === value || null === value.data) return '';
  if (isMediaLinkAttribute(attribute) && isMediaLinkData(value.data)) return value.data;
  if (isMediaFileAttribute(attribute) && isMediaFileData(value.data)) return value.data.filePath;

  return '';
};

export default Data;
