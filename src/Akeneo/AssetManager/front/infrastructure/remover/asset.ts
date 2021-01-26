import AssetRemover from 'akeneoassetmanager/domain/remover/asset';
import AssetCode, {assetCodeStringValue} from 'akeneoassetmanager/domain/model/asset/code';
import AssetFamilyIdentifier, {
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {deleteJSON} from 'akeneoassetmanager/tools/fetch';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import errorHandler from 'akeneoassetmanager/infrastructure/tools/error-handler';
import {Query} from 'akeneoassetmanager/domain/fetcher/fetcher';

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

  async removeFromQuery(assetFamilyIdentifier: AssetFamilyIdentifier, query: Query): Promise<void> {
    return await deleteJSON(
      routing.generate('akeneo_asset_manager_asset_mass_delete_rest', {
        assetFamilyIdentifier: denormalizeAssetFamilyIdentifier(assetFamilyIdentifier),
      }),
      query
    ).catch(errorHandler);
  }
}

export default new AssetRemoverImplementation();
