import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import LabelCollection from 'akeneoassetmanager/domain/model/label-collection';
import {NormalizedMinimalValue} from 'akeneoassetmanager/domain/model/asset/value';

export interface CreationAsset {
  assetFamilyIdentifier: string;
  code: AssetCode;
  labels: LabelCollection;
  values: NormalizedMinimalValue[];
}
