import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {ValidationError} from '@akeneo-pim-community/shared';
import {AssetFamilyCreation} from 'akeneoassetmanager/domain/model/asset-family/creation';
import AssetFamilyIdentifier, {
  assetFamilyIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {handleResponse} from 'akeneoassetmanager/infrastructure/tools/handleResponse';

export interface AssetFamilySaver {
  save: (entity: AssetFamily) => Promise<ValidationError[] | null>;
  create: (entity: AssetFamilyCreation) => Promise<ValidationError[] | null>;
}

const generateAssetFamilyEditUrl = (assetFamilyIdentifier: AssetFamilyIdentifier) =>
  `/rest/asset_manager/${assetFamilyIdentifier}`;
const generateAssetFamilyCreateUrl = () => `/rest/asset_manager`;

export class AssetFamilySaverImplementation implements AssetFamilySaver {
  constructor() {
    Object.freeze(this);
  }

  async save(assetFamily: AssetFamily): Promise<ValidationError[] | null> {
    const response = await fetch(generateAssetFamilyEditUrl(assetFamilyIdentifierStringValue(assetFamily.identifier)), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(assetFamily),
    });

    return await handleResponse(response);
  }

  async create(assetFamilyCreation: AssetFamilyCreation): Promise<ValidationError[] | null> {
    const response = await fetch(generateAssetFamilyCreateUrl(), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify(assetFamilyCreation),
    });

    return await handleResponse(response);
  }
}

const assetFamilySaver = new AssetFamilySaverImplementation();
export default assetFamilySaver;
