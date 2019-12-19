import MediaFileData, {NormalizedMediaFileData} from 'akeneoassetmanager/domain/model/asset/data/media-file';
import MediaLinkData, {NormalizedMediaLinkData} from 'akeneoassetmanager/domain/model/asset/data/media-link';
import NumberData, {NormalizedNumberData} from 'akeneoassetmanager/domain/model/asset/data/number';
import OptionCollectionData from 'akeneoassetmanager/domain/model/asset/data/option-collection';
import OptionData from 'akeneoassetmanager/domain/model/asset/data/option';
import TextData, {NormalizedTextData} from 'akeneoassetmanager/domain/model/asset/data/text';
import {NormalizedOptionData} from 'akeneoreferenceentity/domain/model/record/data/option';
import {NormalizedOptionCollectionData} from 'akeneoreferenceentity/domain/model/record/data/option-collection';

export type NormalizedData =
  | NormalizedMediaFileData
  | NormalizedMediaLinkData
  | NormalizedNumberData
  | NormalizedOptionData
  | NormalizedOptionCollectionData
  | NormalizedTextData;

type Data = MediaFileData | MediaLinkData | NumberData | OptionCollectionData | OptionData | TextData;

export default Data;
