import notify from 'akeneoassetmanager/tools/notify';

const FILE_UPLOAD_LIMIT = 500;

export default (files: File[], currentQuantity: number): File[] => {
  if (currentQuantity + files.length > FILE_UPLOAD_LIMIT) {
    notify('warning', 'pim_asset_manager.asset.upload.files_limit', {limit: FILE_UPLOAD_LIMIT}, {delay: 8000});
  }

  if (currentQuantity >= FILE_UPLOAD_LIMIT) {
    return [];
  }

  return files.slice(0, FILE_UPLOAD_LIMIT - currentQuantity);
};
