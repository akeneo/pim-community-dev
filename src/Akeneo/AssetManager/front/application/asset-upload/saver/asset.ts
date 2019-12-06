import {CreationAsset} from 'akeneoassetmanager/application/asset-upload/model/asset';
import {NormalizedValidationError as ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import assetSaver from 'akeneoassetmanager/infrastructure/saver/asset';
import {addQueueSupport} from 'akeneoassetmanager/tools/queue';

const MAX_QUEUE_SIZE = 5;
const createWithQueue = addQueueSupport(assetSaver.create, MAX_QUEUE_SIZE);

export const create = (asset: CreationAsset): Promise<ValidationError[] | null> => {
  return createWithQueue(asset);
};
