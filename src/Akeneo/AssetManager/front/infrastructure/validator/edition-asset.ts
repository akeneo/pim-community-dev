import {validateAgainstSchema} from '@akeneo-pim-community/legacy-bridge';
import {BackendEditionAsset} from 'akeneoassetmanager/infrastructure/model/edition-asset';
import editionAssetSchema from 'akeneoassetmanager/infrastructure/model/edition-asset.schema.json';

export const validateBackendEditionAsset = (data: any): BackendEditionAsset =>
  validateAgainstSchema<BackendEditionAsset>(data, editionAssetSchema);
