import {CreationAsset} from 'akeneoassetmanager/application/asset-upload/model/creation-asset';
import {ValidationError} from '@akeneo-pim-community/shared';
import assetSaver from 'akeneoassetmanager/infrastructure/saver/asset';
import createQueue from 'p-limit';

const CONCURRENCY = 5;
const queue = createQueue(CONCURRENCY);

export const create = (asset: CreationAsset): Promise<ValidationError[] | null> => {
  return queue(() => assetSaver.create(asset));
};
