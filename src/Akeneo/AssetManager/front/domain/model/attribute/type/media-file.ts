import AttributeIdentifier, {
  denormalizeAttributeIdentifier,
} from 'akeneoassetmanager/domain/model/attribute/identifier';
import AssetFamilyIdentifier, {
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection, {denormalizeLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import AttributeCode, {denormalizeAttributeCode} from 'akeneoassetmanager/domain/model/attribute/code';
import {NormalizedAttribute, Attribute, ConcreteAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {MaxFileSize} from 'akeneoassetmanager/domain/model/attribute/type/media-file/max-file-size';
import {AllowedExtensions} from 'akeneoassetmanager/domain/model/attribute/type/media-file/allowed-extensions';
import {MediaType} from 'akeneoassetmanager/domain/model/attribute/type/media-file/media-type';

export const MEDIA_FILE_ATTRIBUTE_TYPE = 'media_file';

export interface NormalizedMediaFileAttribute extends NormalizedAttribute {
  type: 'media_file';
  allowed_extensions: AllowedExtensions;
  max_file_size: MaxFileSize;
  media_type: MediaType;
}

export type NormalizedMediaFileAdditionalProperty = MaxFileSize | AllowedExtensions | MediaType;

export type MediaFileAdditionalProperty = MaxFileSize | AllowedExtensions | MediaType;

export interface MediaFileAttribute extends Attribute {
  maxFileSize: MaxFileSize;
  allowedExtensions: AllowedExtensions;
  mediaType: MediaType;
  normalize(): NormalizedMediaFileAttribute;
}

export class InvalidArgumentError extends Error {}

export class ConcreteMediaFileAttribute extends ConcreteAttribute implements MediaFileAttribute {
  private constructor(
    identifier: AttributeIdentifier,
    assetFamilyIdentifier: AssetFamilyIdentifier,
    code: AttributeCode,
    labelCollection: LabelCollection,
    valuePerLocale: boolean,
    valuePerChannel: boolean,
    order: number,
    is_required: boolean,
    readonly maxFileSize: MaxFileSize,
    readonly allowedExtensions: AllowedExtensions,
    readonly mediaType: MediaType
  ) {
    super(
      identifier,
      assetFamilyIdentifier,
      code,
      labelCollection,
      'media_file',
      valuePerLocale,
      valuePerChannel,
      order,
      is_required
    );

    Object.freeze(this);
  }

  public static createFromNormalized(normalizedMediaFileAttribute: NormalizedMediaFileAttribute) {
    return new ConcreteMediaFileAttribute(
      denormalizeAttributeIdentifier(normalizedMediaFileAttribute.identifier),
      denormalizeAssetFamilyIdentifier(normalizedMediaFileAttribute.asset_family_identifier),
      denormalizeAttributeCode(normalizedMediaFileAttribute.code),
      denormalizeLabelCollection(normalizedMediaFileAttribute.labels),
      normalizedMediaFileAttribute.value_per_locale,
      normalizedMediaFileAttribute.value_per_channel,
      normalizedMediaFileAttribute.order,
      normalizedMediaFileAttribute.is_required,
      normalizedMediaFileAttribute.max_file_size,
      normalizedMediaFileAttribute.allowed_extensions,
      normalizedMediaFileAttribute.media_type
    );
  }

  public normalize(): NormalizedMediaFileAttribute {
    return {
      ...super.normalize(),
      type: 'media_file',
      max_file_size: this.maxFileSize,
      allowed_extensions: this.allowedExtensions,
      media_type: this.mediaType,
    };
  }
}

export const denormalize = ConcreteMediaFileAttribute.createFromNormalized;
