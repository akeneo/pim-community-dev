import {validateAgainstSchema} from 'akeneoassetmanager/infrastructure/tools/validator';
import {BackendAssetFamily} from 'akeneoassetmanager/infrastructure/model/asset-family';
import schema from 'akeneoassetmanager/infrastructure/model/asset-family.schema.json';

export const validateBackendAssetFamily = (data: any): BackendAssetFamily =>
  validateAgainstSchema<BackendAssetFamily>(data, schema);
