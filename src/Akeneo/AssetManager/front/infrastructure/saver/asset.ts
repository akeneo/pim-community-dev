import Saver from 'akeneoassetmanager/domain/saver/saver';
import Asset from 'akeneoassetmanager/domain/model/asset/asset';
import {postJSON} from 'akeneoassetmanager/tools/fetch';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import handleError from 'akeneoassetmanager/infrastructure/tools/error-handler';

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
        assetFamilyIdentifier: asset.getAssetFamilyIdentifier().stringValue(),
        assetCode: asset.getCode().stringValue(),
      }),
      normalizedAsset
    ).catch(handleError);
  }

  async create(asset: Asset): Promise<ValidationError[] | null> {
    const normalizedAsset = asset.normalize() as any;

    return await postJSON(
      routing.generate('akeneo_asset_manager_asset_create_rest', {
        assetFamilyIdentifier: asset.getAssetFamilyIdentifier().stringValue(),
      }),
      normalizedAsset
    ).catch(handleError);
  }
}

export default new AssetSaverImplementation();
