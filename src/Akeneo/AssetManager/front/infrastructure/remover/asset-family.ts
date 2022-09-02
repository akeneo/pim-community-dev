import AssetFamilyIdentifier, {
  assetFamilyIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {ValidationError} from '@akeneo-pim-community/shared';
import Remover from 'akeneoassetmanager/domain/remover/remover';
import {handleResponse} from 'akeneoassetmanager/infrastructure/tools/handleResponse';

const generateRemoveUrl = (assetFamilyIdentifier: AssetFamilyIdentifier) =>
  `/rest/asset_manager/${assetFamilyIdentifier}`;

export interface AssetFamilyRemover extends Remover<AssetFamilyIdentifier> {}

export class AssetFamilyRemoverImplementation implements AssetFamilyRemover {
  constructor() {
    Object.freeze(this);
  }

  async remove(attributeIdentifier: AssetFamilyIdentifier): Promise<ValidationError[] | null> {
    const response = await fetch(generateRemoveUrl(assetFamilyIdentifierStringValue(attributeIdentifier)), {
      method: 'DELETE',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    });

    return await handleResponse(response);
  }
}

export default new AssetFamilyRemoverImplementation();
