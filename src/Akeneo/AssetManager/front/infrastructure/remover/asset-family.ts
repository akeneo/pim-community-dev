import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {deleteJSON} from 'akeneoassetmanager/tools/fetch';
import {ValidationError} from '@akeneo-pim-community/shared';
import Remover from 'akeneoassetmanager/domain/remover/remover';
import errorHandler from 'akeneoassetmanager/infrastructure/tools/error-handler';
import {attributeIdentifierStringValue} from 'akeneoassetmanager/domain/model/attribute/identifier';

const routing = require('routing');

export interface AssetFamilyRemover extends Remover<AssetFamilyIdentifier> {}

export class AssetFamilyRemoverImplementation implements AssetFamilyRemover {
  constructor() {
    Object.freeze(this);
  }

  async remove(attributeIdentifier: AssetFamilyIdentifier): Promise<ValidationError[] | null> {
    return await deleteJSON(
      routing.generate('akeneo_asset_manager_asset_family_delete_rest', {
        identifier: attributeIdentifierStringValue(attributeIdentifier),
      })
    ).catch(errorHandler);
  }
}

export default new AssetFamilyRemoverImplementation();
