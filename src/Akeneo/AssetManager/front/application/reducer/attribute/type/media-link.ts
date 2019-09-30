import {
  NormalizedMediaLinkAttribute,
  NormalizedMediaLinkAdditionalProperty,
} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {NormalizedPrefix} from 'akeneoassetmanager/domain/model/attribute/type/media-link/prefix';
import {NormalizedSuffix} from 'akeneoassetmanager/domain/model/attribute/type/media-link/suffix';
import {NormalizedMediaType} from 'akeneoassetmanager/domain/model/attribute/type/media-link/media-type';

const mediaLinkAttributeReducer = (
  normalizedAttribute: NormalizedMediaLinkAttribute,
  propertyCode: string,
  propertyValue: NormalizedMediaLinkAdditionalProperty
): NormalizedMediaLinkAttribute => {
  switch (propertyCode) {
    case 'prefix':
      return {...normalizedAttribute, prefix: propertyValue as NormalizedPrefix};
    case 'suffix':
      return {...normalizedAttribute, suffix: propertyValue as NormalizedSuffix};
    case 'media_type':
      return {...normalizedAttribute, media_type: propertyValue as NormalizedMediaType};
    default:
      break;
  }

  return normalizedAttribute;
};

export const reducer = mediaLinkAttributeReducer;
