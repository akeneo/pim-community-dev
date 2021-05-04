import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {postJSON} from 'akeneoassetmanager/tools/fetch';
import {ValidationError} from '@akeneo-pim-community/shared';
import handleError from 'akeneoassetmanager/infrastructure/tools/error-handler';
import {assetFamilyIdentifierStringValue} from 'akeneoassetmanager/domain/model/asset-family/identifier';

const routing = require('routing');

export interface ComputeTransformationsLauncher {
  launch: (entity: AssetFamily) => Promise<ValidationError[] | null>;
}

export class ComputeTransformationsLauncherImplementation implements ComputeTransformationsLauncher {
  constructor() {
    Object.freeze(this);
  }

  async launch(assetFamily: AssetFamily): Promise<ValidationError[] | null> {
    return await postJSON(
      routing.generate('akeneo_asset_manager_asset_family_compute_transformations_rest', {
        identifier: assetFamilyIdentifierStringValue(assetFamily.identifier),
      }),
      {}
    ).catch(handleError);
  }
}

export default new ComputeTransformationsLauncherImplementation();
