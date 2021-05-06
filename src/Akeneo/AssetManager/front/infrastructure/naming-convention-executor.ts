import {postJSON} from 'akeneoassetmanager/tools/fetch';
import {ValidationError} from '@akeneo-pim-community/shared';
import handleError from 'akeneoassetmanager/infrastructure/tools/error-handler';
import {assetFamilyIdentifierStringValue} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {assetCodeStringValue} from 'akeneoassetmanager/domain/model/asset/code';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';

const routing = require('routing');

export interface NamingConventionExecutor {
  execute: (assetFamilyIdentifier: AssetFamilyIdentifier, assetCode: AssetCode) => Promise<ValidationError[] | null>;
}

export class NamingConventionExecutorImplementation implements NamingConventionExecutor {
  constructor() {
    Object.freeze(this);
  }

  async execute(assetFamilyIdentifier: AssetFamilyIdentifier, assetCode: AssetCode): Promise<ValidationError[] | null> {
    return await postJSON(
      routing.generate('akeneo_asset_manager_asset_execute_naming_convention', {
        assetFamilyIdentifier: assetFamilyIdentifierStringValue(assetFamilyIdentifier),
        assetCode: assetCodeStringValue(assetCode),
      }),
      {}
    ).catch(handleError);
  }

  async executeAll(assetFamilyIdentifier: AssetFamilyIdentifier): Promise<ValidationError[] | null> {
    return await postJSON(
      routing.generate('akeneo_asset_manager_asset_family_execute_naming_convention', {
        assetFamilyIdentifier: assetFamilyIdentifierStringValue(assetFamilyIdentifier),
      }),
      {}
    ).catch(handleError);
  }
}

export default new NamingConventionExecutorImplementation();
