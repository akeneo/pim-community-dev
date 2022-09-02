import AttributeRemover from 'akeneoassetmanager/domain/remover/attribute';
import AttributeIdentifier, {
  attributeIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/attribute/identifier';
import {ValidationError} from '@akeneo-pim-community/shared';
import AssetFamilyIdentifier, {
  assetFamilyIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {handleResponse} from 'akeneoassetmanager/infrastructure/tools/handleResponse';

const generateDeleteAttributeUrl = (
  assetFamilyIdentifier: AssetFamilyIdentifier,
  attributeIdentifier: AttributeIdentifier
) => `/rest/asset_manager/${assetFamilyIdentifier}/attribute/${attributeIdentifier}`;

export class AttributeRemoverImplementation implements AttributeRemover<AssetFamilyIdentifier, AttributeIdentifier> {
  constructor() {
    Object.freeze(this);
  }

  async remove(
    assetFamilyIdentifier: AssetFamilyIdentifier,
    attributeIdentifier: AttributeIdentifier
  ): Promise<ValidationError[] | null> {
    const response = await fetch(
      generateDeleteAttributeUrl(
        assetFamilyIdentifierStringValue(assetFamilyIdentifier),
        attributeIdentifierStringValue(attributeIdentifier)
      ),
      {
        method: 'DELETE',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
      }
    );

    return await handleResponse(response);
  }
}

export default new AttributeRemoverImplementation();
