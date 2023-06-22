const MediaField = require('pim/form/common/fields/media');

class ImageMediaField extends MediaField {
  readonly uploadRouteName = 'akeneo_file_storage_upload_image';
}

export = ImageMediaField;
