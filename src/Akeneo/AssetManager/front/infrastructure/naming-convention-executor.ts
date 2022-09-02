import {ValidationError} from '@akeneo-pim-community/shared';
import {assetFamilyIdentifierStringValue} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {assetCodeStringValue} from 'akeneoassetmanager/domain/model/asset/code';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {handleResponse} from './tools/handleResponse';

export interface NamingConventionExecutor {
  execute: (assetFamilyIdentifier: AssetFamilyIdentifier, assetCode: AssetCode) => Promise<ValidationError[] | null>;
}

const generateExecuteNamingConventionUrl = (assetFamilyIdentifier: string, assetCode: string) =>
  `/rest/asset_manager/${assetFamilyIdentifier}/asset/${assetCode}/execute_naming_convention`;
const generateExecuteAllNamingConventionUrl = (assetFamilyIdentifier: string) =>
  `/rest/asset_manager/${assetFamilyIdentifier}/execute_naming_convention`;

export class NamingConventionExecutorImplementation implements NamingConventionExecutor {
  constructor() {
    Object.freeze(this);
  }

  async execute(assetFamilyIdentifier: AssetFamilyIdentifier, assetCode: AssetCode): Promise<ValidationError[] | null> {
    const response = await fetch(
      generateExecuteNamingConventionUrl(
        assetFamilyIdentifierStringValue(assetFamilyIdentifier),
        assetCodeStringValue(assetCode)
      ),
      {
        method: 'POST',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
      }
    );

    return await handleResponse(response);
  }

  async executeAll(assetFamilyIdentifier: AssetFamilyIdentifier): Promise<ValidationError[] | null> {
    const response = await fetch(
      generateExecuteAllNamingConventionUrl(assetFamilyIdentifierStringValue(assetFamilyIdentifier)),
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

export default new NamingConventionExecutorImplementation();
