'use strict';

define(['pim/media-field'], function (MediaField) {
  return MediaField.extend({
    uploadRouteName: 'akeneo_file_storage_upload_image',
  });
});
