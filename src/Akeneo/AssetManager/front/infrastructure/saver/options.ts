import {ValidationError} from '@akeneo-pim-community/shared';
import {Attribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {AttributeWithOptions, OptionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option';
import {Option} from 'akeneoassetmanager/domain/model/attribute/type/option/option';
import AttributeIdentifier, {
  attributeIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/attribute/identifier';
import AssetFamilyIdentifier, {
  assetFamilyIdentifierStringValue,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {handleResponse} from 'akeneoassetmanager/infrastructure/tools/handleResponse';

const generateAssetAttributeEditUrl = (
  assetFamilyIdentifier: AssetFamilyIdentifier,
  attributeIdentifier: AttributeIdentifier
) => `/rest/asset_manager/${assetFamilyIdentifier}/attribute/${attributeIdentifier}`;

export class AttributeOptionSaver {
  constructor() {
    Object.freeze(this);
  }

  async save(attribute: Attribute): Promise<ValidationError[] | null> {
    const normalizedOptionAttribute = (attribute as OptionAttribute).normalize() as any;

    const response = await fetch(
      generateAssetAttributeEditUrl(
        assetFamilyIdentifierStringValue((attribute as OptionAttribute).getAssetFamilyIdentifier()),
        attributeIdentifierStringValue((attribute as OptionAttribute).getIdentifier())
      ),
      {
        method: 'POST',
        cache: 'no-cache',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          identifier: normalizedOptionAttribute.identifier,
          asset_family_identifier: normalizedOptionAttribute.asset_family_identifier,
          options: ((attribute as any) as AttributeWithOptions).getOptions().map((option: Option) => option),
        }),
      }
    );

    return await handleResponse(response);
  }
}

const attributeOptionSaver = new AttributeOptionSaver();
export default attributeOptionSaver;
