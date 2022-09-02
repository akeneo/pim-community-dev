import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {ValidationError} from '@akeneo-pim-community/shared';
import {assetFamilyIdentifierStringValue} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {handleResponse} from 'akeneoassetmanager/infrastructure/tools/handleResponse';

const generateComputeTransformationUrl = (assetFamilyCode: string) =>
  `/rest/asset_manager/${assetFamilyCode}/compute_transformations`;

export interface ComputeTransformationsLauncher {
  launch: (entity: AssetFamily) => Promise<ValidationError[] | null>;
}

export class ComputeTransformationsLauncherImplementation implements ComputeTransformationsLauncher {
  constructor() {
    Object.freeze(this);
  }

  async launch(assetFamily: AssetFamily): Promise<ValidationError[] | null> {
    const response = await fetch(
      generateComputeTransformationUrl(assetFamilyIdentifierStringValue(assetFamily.identifier)),
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

export default new ComputeTransformationsLauncherImplementation();
