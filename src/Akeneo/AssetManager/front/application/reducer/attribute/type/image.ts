import {
  NormalizedImageAttribute,
  NormalizedImageAdditionalProperty,
} from 'akeneoassetmanager/domain/model/attribute/type/image';
import {NormalizedMaxFileSize} from 'akeneoassetmanager/domain/model/attribute/type/image/max-file-size';
import {NormalizedAllowedExtensions} from 'akeneoassetmanager/domain/model/attribute/type/image/allowed-extensions';

const imageAttributeReducer = (
  normalizedAttribute: NormalizedImageAttribute,
  propertyCode: string,
  propertyValue: NormalizedImageAdditionalProperty
): NormalizedImageAttribute => {
  switch (propertyCode) {
    case 'max_file_size':
      const max_file_size = propertyValue as NormalizedMaxFileSize;
      return {...normalizedAttribute, max_file_size};
    case 'allowed_extensions':
      const allowed_extensions = propertyValue as NormalizedAllowedExtensions;
      return {...normalizedAttribute, allowed_extensions};

    default:
      break;
  }

  return normalizedAttribute;
};

export const reducer = imageAttributeReducer;
