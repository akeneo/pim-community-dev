import {
  AssetFamily as AssetFamilyModel,
  createEmptyAssetFamily,
} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';

//Still not perfect but it's a first step
export type AssetFamily = AssetFamilyModel & {
  attributes: (NormalizedAttribute & any)[];
};

export const emptyAssetFamily = (): AssetFamily => ({
  ...createEmptyAssetFamily(),
  attributes: [],
});
