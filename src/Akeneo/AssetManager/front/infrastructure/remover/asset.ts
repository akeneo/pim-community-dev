import AssetRemover from 'akeneoassetmanager/domain/remover/asset';
import AssetCode, {assetCodeStringValue} from 'akeneoassetmanager/domain/model/asset/code';
import AssetFamilyIdentifier, {
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {deleteJSON} from 'akeneoassetmanager/tools/fetch';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import errorHandler from 'akeneoassetmanager/infrastructure/tools/error-handler';

const routing = require('routing');

export class AssetRemoverImplementation implements AssetRemover<AssetFamilyIdentifier, AssetCode> {
  constructor() {
    Object.freeze(this);
  }

  async remove(assetFamilyIdentifier: AssetFamilyIdentifier, assetCode: AssetCode): Promise<ValidationError[] | null> {
    return await deleteJSON(
      routing.generate('akeneo_asset_manager_asset_delete_rest', {
        assetCode: assetCodeStringValue(assetCode),
        assetFamilyIdentifier: denormalizeAssetFamilyIdentifier(assetFamilyIdentifier),
      })
    ).catch(errorHandler);
  }

  async removeAll(assetFamilyIdentifier: AssetFamilyIdentifier): Promise<ValidationError[] | null> {
    return await deleteJSON(
      routing.generate('akeneo_asset_manager_asset_delete_all_rest', {
        assetFamilyIdentifier: denormalizeAssetFamilyIdentifier(assetFamilyIdentifier),
      })
    ).catch(errorHandler);
  }
}

export default new AssetRemoverImplementation();
