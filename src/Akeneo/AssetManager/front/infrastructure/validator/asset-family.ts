import {validateAgainstSchema} from '@akeneo-pim-community/legacy-bridge';
import {BackendAssetFamily} from 'akeneoassetmanager/infrastructure/model/asset-family';
import schema from 'akeneoassetmanager/infrastructure/model/asset-family.schema.json';

export const validateBackendAssetFamily = (data: any): BackendAssetFamily =>
  validateAgainstSchema<BackendAssetFamily>(data, schema);
