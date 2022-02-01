import AssetRemover from 'akeneoassetmanager/domain/remover/asset';
import AssetCode, {assetCodeStringValue} from 'akeneoassetmanager/domain/model/asset/code';
import AssetFamilyIdentifier, {
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {ValidationError} from '@akeneo-pim-community/shared';
import {Query} from 'akeneoassetmanager/domain/fetcher/fetcher';
import {handleResponse} from 'akeneoassetmanager/infrastructure/tools/handleResponse';

const generateRemoveUrl = (assetFamilyIdentifier: AssetFamilyIdentifier, assetCode: AssetCode) =>
  `/rest/asset_manager/${assetFamilyIdentifier}/asset/${assetCode}`;
const generateRemoveFromQueryUrl = (assetFamilyIdentifier: AssetFamilyIdentifier) =>
  `/rest/asset_manager/${assetFamilyIdentifier}/assets`;

export class AssetRemoverImplementation implements AssetRemover<AssetFamilyIdentifier, AssetCode> {
  constructor() {
    Object.freeze(this);
  }

  async remove(assetFamilyIdentifier: AssetFamilyIdentifier, assetCode: AssetCode): Promise<ValidationError[] | null> {
    const response = await fetch(
      generateRemoveUrl(denormalizeAssetFamilyIdentifier(assetFamilyIdentifier), assetCodeStringValue(assetCode)),
      {
        method: 'DELETE',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
      }
    );

    return await handleResponse(response);
  }

  async removeFromQuery(assetFamilyIdentifier: AssetFamilyIdentifier, query: Query): Promise<Response> {
    const url = generateRemoveFromQueryUrl(denormalizeAssetFamilyIdentifier(assetFamilyIdentifier));

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
