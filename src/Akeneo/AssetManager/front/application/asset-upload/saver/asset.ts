import {CreationAsset} from 'akeneoassetmanager/application/asset-upload/model/asset';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import assetSaver from 'akeneoassetmanager/infrastructure/saver/asset';
import {addQueueSupport} from 'akeneoassetmanager/tools/queue';
import hydrator from 'akeneoassetmanager/application/hydrator/asset';

const MAX_QUEUE_SIZE = 5;
const createWithQueue = addQueueSupport(assetSaver.create, MAX_QUEUE_SIZE);

export const create = (asset: CreationAsset): Promise<ValidationError[] | null> => {
  const hydratedAsset = hydrator({
    asset_family_identifier: asset.assetFamilyIdentifier,
    code: asset.code,
    labels: asset.labels,
    values: asset.values,
  });

  return createWithQueue(hydratedAsset);
};
