import Saver from 'akeneoassetmanager/domain/saver/saver';
import Asset from 'akeneoassetmanager/domain/model/asset/asset';
import {postJSON} from 'akeneoassetmanager/tools/fetch';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import handleError from 'akeneoassetmanager/infrastructure/tools/error-handler';
import {assetCodeStringValue} from 'akeneoassetmanager/domain/model/asset/code';
import {assetFamilyIdentifierStringValue} from 'akeneoassetmanager/domain/model/asset-family/identifier';

const routing = require('routing');

export interface AssetSaver extends Saver<Asset> {}

export class AssetSaverImplementation implements AssetSaver {
  constructor() {
    Object.freeze(this);
  }

  async save(asset: Asset): Promise<ValidationError[] | null> {
    const normalizedAsset = asset.normalizeMinimal();

    return await postJSON(
      routing.generate('akeneo_asset_manager_asset_edit_rest', {
        assetFamilyIdentifier: assetFamilyIdentifierStringValue(asset.getAssetFamilyIdentifier()),
        assetCode: assetCodeStringValue(asset.getCode()),
      }),
      normalizedAsset
    ).catch(handleError);
  }

  async create(asset: Asset): Promise<ValidationError[] | null> {
    const normalizedAsset = asset.normalize() as any;

    return await postJSON(
      routing.generate('akeneo_asset_manager_asset_create_rest', {
        assetFamilyIdentifier: assetFamilyIdentifierStringValue(asset.getAssetFamilyIdentifier()),
      }),
      normalizedAsset
    ).catch(handleError);
  }
}

export default new AssetSaverImplementation();
