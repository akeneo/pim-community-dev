import Saver from 'akeneoassetmanager/domain/saver/attribute';
import {postJSON} from 'akeneoassetmanager/tools/fetch';
import {ValidationError} from '@akeneo-pim-community/shared';
import MinimalAttribute from 'akeneoassetmanager/domain/model/attribute/minimal';
import handleError from 'akeneoassetmanager/infrastructure/tools/error-handler';
import {Attribute, NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {assetFamilyIdentifierStringValue} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {attributeIdentifierStringValue} from 'akeneoassetmanager/domain/model/attribute/identifier';

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
        assetFamilyIdentifier: assetFamilyIdentifierStringValue(attribute.getAssetFamilyIdentifier()),
        attributeIdentifier: attributeIdentifierStringValue(attribute.getIdentifier()),
      }),
      attribute.normalize()
    ).catch(handleError);
  }

  async create(attribute: MinimalAttribute): Promise<ValidationError[] | null> {
    const normalizedAttribute = attribute.normalize() as NormalizedAttribute;

    return await postJSON(
      routing.generate('akeneo_asset_manager_attribute_create_rest', {
        assetFamilyIdentifier: assetFamilyIdentifierStringValue(attribute.getAssetFamilyIdentifier()),
      }),
      normalizedAttribute
    ).catch(handleError);
  }
}

export default new AttributeSaverImplementation();
