import {validateAgainstSchema} from '@akeneo-pim-community/legacy-bridge';
import {BackendListAsset} from 'akeneoassetmanager/infrastructure/model/list-asset';
import listAssetSchema from 'akeneoassetmanager/infrastructure/model/list-asset.schema.json';

export const validateBackendListAsset = (data: any): BackendListAsset =>
  validateAgainstSchema<BackendListAsset>(data, listAssetSchema);
