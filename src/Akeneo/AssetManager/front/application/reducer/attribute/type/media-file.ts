import {
  NormalizedMediaFileAttribute,
  NormalizedMediaFileAdditionalProperty,
} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {MaxFileSize} from 'akeneoassetmanager/domain/model/attribute/type/media-file/max-file-size';
import {AllowedExtensions} from 'akeneoassetmanager/domain/model/attribute/type/media-file/allowed-extensions';
import {MediaType} from 'akeneoassetmanager/domain/model/attribute/type/media-file/media-type';

const mediaFileAttributeReducer = (
  normalizedAttribute: NormalizedMediaFileAttribute,
  propertyCode: string,
  propertyValue: NormalizedMediaFileAdditionalProperty
): NormalizedMediaFileAttribute => {
  switch (propertyCode) {
    case 'max_file_size':
      const max_file_size = propertyValue as MaxFileSize;
      return {...normalizedAttribute, max_file_size};
    case 'allowed_extensions':
      const allowed_extensions = propertyValue as AllowedExtensions;
      return {...normalizedAttribute, allowed_extensions};
    case 'media_type':
      const media_type = propertyValue as MediaType;
      return {...normalizedAttribute, media_type};

    default:
      break;
  }

  return normalizedAttribute;
};

export const reducer = mediaFileAttributeReducer;
