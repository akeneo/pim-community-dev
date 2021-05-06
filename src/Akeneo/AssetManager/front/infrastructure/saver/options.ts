import {postJSON} from 'akeneoassetmanager/tools/fetch';
import {ValidationError} from '@akeneo-pim-community/shared';
import handleError from 'akeneoassetmanager/infrastructure/tools/error-handler';
import {Attribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {AttributeWithOptions, OptionAttribute} from 'akeneoassetmanager/domain/model/attribute/type/option';
import {Option} from 'akeneoassetmanager/domain/model/attribute/type/option/option';
import {attributeIdentifierStringValue} from 'akeneoassetmanager/domain/model/attribute/identifier';
import {assetFamilyIdentifierStringValue} from 'akeneoassetmanager/domain/model/asset-family/identifier';

const routing = require('routing');

export class AttributeOptionSaver {
  constructor() {
    Object.freeze(this);
  }

  async save(attribute: Attribute): Promise<ValidationError[] | null> {
    const normalizedOptionAttribute = (attribute as OptionAttribute).normalize() as any;

    return await postJSON(
      routing.generate('akeneo_asset_manager_attribute_edit_rest', {
        assetFamilyIdentifier: assetFamilyIdentifierStringValue(
          (attribute as OptionAttribute).getAssetFamilyIdentifier()
        ),
        attributeIdentifier: attributeIdentifierStringValue((attribute as OptionAttribute).getIdentifier()),
      }),
      {
        identifier: normalizedOptionAttribute.identifier,
        asset_family_identifier: normalizedOptionAttribute.asset_family_identifier,
        options: ((attribute as any) as AttributeWithOptions).getOptions().map((option: Option) => option),
      }
    ).catch(handleError);
  }
}

const attributeOptionSaver = new AttributeOptionSaver();
export default attributeOptionSaver;
