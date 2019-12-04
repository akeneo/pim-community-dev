import {CreationAsset} from 'akeneoassetmanager/application/asset-upload/model/asset';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import assetSaver from 'akeneoassetmanager/infrastructure/saver/asset';

export const create = (asset: CreationAsset): Promise<ValidationError | null> => {

};
