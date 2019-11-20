import AttributeIdentifier, {
  denormalizeAttributeIdentifier,
} from 'akeneoassetmanager/domain/model/attribute/identifier';
import AssetFamilyIdentifier, {
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection, {denormalizeLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import AttributeCode, {denormalizeAttributeCode} from 'akeneoassetmanager/domain/model/attribute/code';
import {NormalizedAttribute, Attribute, ConcreteAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {MaxFileSize} from 'akeneoassetmanager/domain/model/attribute/type/image/max-file-size';
import {AllowedExtensions} from 'akeneoassetmanager/domain/model/attribute/type/image/allowed-extensions';

export const IMAGE_ATTRIBUTE_TYPE = 'image';

export interface NormalizedImageAttribute extends NormalizedAttribute {
  type: 'image';
  allowed_extensions: AllowedExtensions;
  max_file_size: MaxFileSize;
}

export type NormalizedImageAdditionalProperty = MaxFileSize | AllowedExtensions;

export type ImageAdditionalProperty = MaxFileSize | AllowedExtensions;

export interface ImageAttribute extends Attribute {
  maxFileSize: MaxFileSize;
  allowedExtensions: AllowedExtensions;
  normalize(): NormalizedImageAttribute;
}

export class InvalidArgumentError extends Error {}

export class ConcreteImageAttribute extends ConcreteAttribute implements ImageAttribute {
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
    readonly allowedExtensions: AllowedExtensions
  ) {
    super(
      identifier,
      assetFamilyIdentifier,
      code,
      labelCollection,
      'image',
      valuePerLocale,
      valuePerChannel,
      order,
      is_required
    );

    Object.freeze(this);
  }

  public static createFromNormalized(normalizedImageAttribute: NormalizedImageAttribute) {
    return new ConcreteImageAttribute(
      denormalizeAttributeIdentifier(normalizedImageAttribute.identifier),
      denormalizeAssetFamilyIdentifier(normalizedImageAttribute.asset_family_identifier),
      denormalizeAttributeCode(normalizedImageAttribute.code),
      denormalizeLabelCollection(normalizedImageAttribute.labels),
      normalizedImageAttribute.value_per_locale,
      normalizedImageAttribute.value_per_channel,
      normalizedImageAttribute.order,
      normalizedImageAttribute.is_required,
      normalizedImageAttribute.max_file_size,
      normalizedImageAttribute.allowed_extensions
    );
  }

  public normalize(): NormalizedImageAttribute {
    return {
      ...super.normalize(),
      type: 'image',
      max_file_size: this.maxFileSize,
      allowed_extensions: this.allowedExtensions,
    };
  }
}

export const denormalize = ConcreteImageAttribute.createFromNormalized;
