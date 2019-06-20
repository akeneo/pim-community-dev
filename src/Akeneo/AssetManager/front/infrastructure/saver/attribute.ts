import Saver from 'akeneoassetmanager/domain/saver/attribute';
import {postJSON} from 'akeneoassetmanager/tools/fetch';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import MinimalAttribute from 'akeneoassetmanager/domain/model/attribute/minimal';
import handleError from 'akeneoassetmanager/infrastructure/tools/error-handler';
import {Attribute, NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';

const routing = require('routing');

export interface AttributeSaver extends Saver<MinimalAttribute, Attribute> {}

export class AttributeSaverImplementation implements AttributeSaver {
  constructor() {
    Object.freeze(this);
  }

  async save(attribute: Attribute): Promise<ValidationError[] | null> {
    const normalizedAttribute = attribute.normalize() as any;
    normalizedAttribute.identifier = {
      identifier: normalizedAttribute.identifier,
      asset_family_identifier: normalizedAttribute.asset_family_identifier,
    };

    return await postJSON(
      routing.generate('akeneo_asset_manager_attribute_edit_rest', {
        assetFamilyIdentifier: attribute.getAssetFamilyIdentifier().stringValue(),
        attributeIdentifier: attribute.getIdentifier().identifier,
      }),
      attribute.normalize()
    ).catch(handleError);
  }

  async create(attribute: MinimalAttribute): Promise<ValidationError[] | null> {
    const normalizedAttribute = attribute.normalize() as NormalizedAttribute;

    return await postJSON(
      routing.generate('akeneo_asset_manager_attribute_create_rest', {
        assetFamilyIdentifier: attribute.getAssetFamilyIdentifier().stringValue(),
      }),
      normalizedAttribute
    ).catch(handleError);
  }
}

export default new AttributeSaverImplementation();
