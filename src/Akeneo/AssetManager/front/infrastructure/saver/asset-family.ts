import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {postJSON} from 'akeneoassetmanager/tools/fetch';
import {ValidationError} from '@akeneo-pim-community/shared';
import handleError from 'akeneoassetmanager/infrastructure/tools/error-handler';
import {AssetFamilyCreation} from 'akeneoassetmanager/domain/model/asset-family/creation';
import {assetFamilyIdentifierStringValue} from 'akeneoassetmanager/domain/model/asset-family/identifier';

const routing = require('routing');

export interface AssetFamilySaver {
  save: (entity: AssetFamily) => Promise<ValidationError[] | null>;
  create: (entity: AssetFamilyCreation) => Promise<ValidationError[] | null>;
}

export class AssetFamilySaverImplementation implements AssetFamilySaver {
  constructor() {
    Object.freeze(this);
  }

  async save(assetFamily: AssetFamily): Promise<ValidationError[] | null> {
    return await postJSON(
      routing.generate('akeneo_asset_manager_asset_family_edit_rest', {
        identifier: assetFamilyIdentifierStringValue(assetFamily.identifier),
      }),
      assetFamily
    ).catch(handleError);
  }

  async create(assetFamilyCreation: AssetFamilyCreation): Promise<ValidationError[] | null> {
    return await postJSON(routing.generate('akeneo_asset_manager_asset_family_create_rest'), assetFamilyCreation).catch(
      handleError
    );
  }
}

export default new AssetFamilySaverImplementation();
