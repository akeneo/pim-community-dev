import Saver from 'akeneoassetmanager/domain/saver/attribute';
import {ValidationError} from '@akeneo-pim-community/shared';
import MinimalAttribute from 'akeneoassetmanager/domain/model/attribute/minimal';
import {Attribute, NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import AssetFamilyIdentifier, {
  assetFamilyIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AttributeIdentifier, {
  attributeIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/attribute/identifier';
import {handleResponse} from 'akeneoassetmanager/infrastructure/tools/handleResponse';

const generateAssetAttributeEditUrl = (
  assetFamilyIdentifier: AssetFamilyIdentifier,
  attributeIdentifier: AttributeIdentifier
) => `/rest/asset_manager/${assetFamilyIdentifier}/attribute/${attributeIdentifier}`;
const generateAssetAttributeCreateUrl = (assetFamilyIdentifier: AssetFamilyIdentifier) =>
  `/rest/asset_manager/${assetFamilyIdentifier}/attribute`;

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

    const response = await fetch(
      generateAssetAttributeEditUrl(
        assetFamilyIdentifierStringValue(attribute.getAssetFamilyIdentifier()),
        attributeIdentifierStringValue(attribute.getIdentifier())
      ),
      {
        method: 'POST',
        cache: 'no-cache',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(attribute.normalize()),
      }
    );

    return await handleResponse(response);
  }

  async create(attribute: MinimalAttribute): Promise<ValidationError[] | null> {
    const normalizedAttribute = attribute.normalize() as NormalizedAttribute;

    const response = await fetch(
      generateAssetAttributeCreateUrl(assetFamilyIdentifierStringValue(attribute.getAssetFamilyIdentifier())),
      {
        method: 'POST',
        cache: 'no-cache',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(normalizedAttribute),
      }
    );

    return await handleResponse(response);
  }
}

const attributeSaver = new AttributeSaverImplementation();
export default attributeSaver;
