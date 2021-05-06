import AssetRemover from 'akeneoassetmanager/domain/remover/asset';
import AssetCode, {assetCodeStringValue} from 'akeneoassetmanager/domain/model/asset/code';
import AssetFamilyIdentifier, {
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {deleteJSON} from 'akeneoassetmanager/tools/fetch';
import {ValidationError} from '@akeneo-pim-community/shared';
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

  async removeFromQuery(assetFamilyIdentifier: AssetFamilyIdentifier, query: Query): Promise<Response> {
    const url = routing.generate('akeneo_asset_manager_asset_mass_delete_rest', {
      assetFamilyIdentifier: denormalizeAssetFamilyIdentifier(assetFamilyIdentifier),
    });

    return await fetch(url, {
      method: 'DELETE',
      body: JSON.stringify(query),
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
    });
  }
}

export default new AssetRemoverImplementation();
