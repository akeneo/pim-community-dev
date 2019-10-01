import {Labels} from 'akeneopimenrichmentassetmanager/platform/model/label';
import {NormalizedFile} from 'akeneoassetmanager/domain/model/file';

export type AssetFamilyIdentifier = string;
type AssetFamilyCode = string;
type AttributeIdentifier = string;

export type AssetFamily = {
  identifier: AssetFamilyIdentifier;
  code: AssetFamilyCode;
  labels: Labels;
  image: NormalizedFile;
  attributeAsLabel: AttributeIdentifier;
  attributeAsImage: AttributeIdentifier;
};

export const emptyAssetFamily = (): AssetFamily => ({
  identifier: '',
  code: '',
  image: {
    filePath: '',
    originalFilename: '',
  },
  labels: {},
  attributeAsLabel: '',
  attributeAsImage: '',
});
