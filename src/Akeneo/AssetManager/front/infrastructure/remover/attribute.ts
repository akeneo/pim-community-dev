import AttributeRemover from 'akeneoassetmanager/domain/remover/attribute';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import {deleteJSON} from 'akeneoassetmanager/tools/fetch';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import errorHandler from 'akeneoassetmanager/infrastructure/tools/error-handler';

const routing = require('routing');

export class AttributeRemoverImplementation implements AttributeRemover<AssetFamilyIdentifier, AttributeIdentifier> {
  constructor() {
    Object.freeze(this);
  }

  async remove(
    assetFamilyIdentifier: AssetFamilyIdentifier,
    attributeIdentifier: AttributeIdentifier
  ): Promise<ValidationError[] | null> {
    return await deleteJSON(
      routing.generate('akeneo_asset_manager_attribute_delete_rest', {
        assetFamilyIdentifier: assetFamilyIdentifier.stringValue(),
        attributeIdentifier: attributeIdentifier.normalize(),
      })
    ).catch(errorHandler);
  }
}

export default new AttributeRemoverImplementation();
