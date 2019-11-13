import {Labels} from 'akeneopimenrichmentassetmanager/platform/model/label';
import {NormalizedFile} from 'akeneoassetmanager/domain/model/file';
import {NormalizedAttribute} from 'web/bundles/akeneoassetmanager/domain/model/attribute/attribute';

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
  attributes: (NormalizedAttribute & any)[];
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
  attributes: [],
});
