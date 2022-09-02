import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {ValidationError} from '@akeneo-pim-community/shared';
import {assetFamilyIdentifierStringValue} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {handleResponse} from 'akeneoassetmanager/infrastructure/tools/handleResponse';

const generateExecuteProductLinkRulesUrl = (assetFamilyIdentifier: string) =>
  `/rest/asset_manager/${assetFamilyIdentifier}/execute_product_link_rules`;

export interface ProductLinkRulesExecutor {
  execute: (entity: AssetFamily) => Promise<ValidationError[] | null>;
}

export class ProductLinkRulesExecutorImplementation implements ProductLinkRulesExecutor {
  constructor() {
    Object.freeze(this);
  }

  async execute(assetFamily: AssetFamily): Promise<ValidationError[] | null> {
    const response = await fetch(
      generateExecuteProductLinkRulesUrl(assetFamilyIdentifierStringValue(assetFamily.identifier)),
      {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
      }
    );

    return await handleResponse(response);
  }
}

export default new ProductLinkRulesExecutorImplementation();
