import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import LabelCollection from 'akeneoassetmanager/domain/model/label-collection';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';

export type CreationAsset = {
  assetFamilyIdentifier: AssetFamilyIdentifier;
  code: AssetCode;
  labels: LabelCollection;
  values: EditionValue[];
};
