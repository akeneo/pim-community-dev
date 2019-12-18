import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import LabelCollection from 'akeneoassetmanager/domain/model/label-collection';
import {NormalizedMinimalValue} from 'akeneoassetmanager/domain/model/asset/value';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';

export type CreationAsset = {
  assetFamilyIdentifier: AssetFamilyIdentifier;
  code: AssetCode;
  labels: LabelCollection;
  values: NormalizedMinimalValue[];
};
