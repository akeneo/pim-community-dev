import {NotificationLevel, Notify, Translate} from '@akeneo-pim-community/shared';

const FILE_UPLOAD_LIMIT = 500;

export default (notify: Notify, translate: Translate, files: File[], currentQuantity: number): File[] => {
  if (currentQuantity + files.length > FILE_UPLOAD_LIMIT) {
    const message = translate('pim_asset_manager.asset.upload.files_limit', {limit: FILE_UPLOAD_LIMIT});
    notify(NotificationLevel.WARNING, message, {delay: 8000});
  }

  if (currentQuantity >= FILE_UPLOAD_LIMIT) {
    return [];
  }

  return files.slice(0, FILE_UPLOAD_LIMIT - currentQuantity);
};
