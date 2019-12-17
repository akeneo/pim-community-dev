import AttributeIdentifier, {
  denormalizeAttributeIdentifier,
} from 'akeneoassetmanager/domain/model/attribute/identifier';
import AssetFamilyIdentifier, {
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection, {denormalizeLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import AttributeCode, {denormalizeAttributeCode} from 'akeneoassetmanager/domain/model/attribute/code';
import {Attribute, ConcreteAttribute, NormalizedAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {
  createPrefixFromNormalized,
  isValidPrefix,
  NormalizedPrefix,
  normalizePrefix,
  Prefix,
} from 'akeneoassetmanager/domain/model/attribute/type/media-link/prefix';
import {
  createSuffixFromNormalized,
  isValidSuffix,
  NormalizedSuffix,
  normalizeSuffix,
  Suffix,
} from 'akeneoassetmanager/domain/model/attribute/type/media-link/suffix';
import {
  createMediaTypeFromNormalized,
  isValidMediaType,
  MediaType,
  NormalizedMediaType,
  normalizeMediaType,
} from 'akeneoassetmanager/domain/model/attribute/type/media-link/media-type';

export type NormalizedMediaLinkAdditionalProperty = NormalizedPrefix | NormalizedSuffix | NormalizedMediaType;
export type MediaLinkAdditionalProperty = Prefix | Suffix | MediaType;

export const MEDIA_LINK_ATTRIBUTE_TYPE = 'media_link';

export interface NormalizedMediaLinkAttribute extends NormalizedAttribute {
  type: typeof MEDIA_LINK_ATTRIBUTE_TYPE;
  prefix: NormalizedPrefix;
  suffix: NormalizedSuffix;
  media_type: NormalizedMediaType;
}

export interface MediaLinkAttribute extends Attribute {
  prefix: Prefix;
  suffix: Suffix;
  mediaType: MediaType;
  normalize(): NormalizedMediaLinkAttribute;
}

export class InvalidArgumentError extends Error {}

export class ConcreteMediaLinkAttribute extends ConcreteAttribute implements MediaLinkAttribute {
  private constructor(
    identifier: AttributeIdentifier,
    assetFamilyIdentifier: AssetFamilyIdentifier,
    code: AttributeCode,
    labelCollection: LabelCollection,
    valuePerLocale: boolean,
    valuePerChannel: boolean,
    order: number,
    is_required: boolean,
    readonly prefix: Prefix,
    readonly suffix: Suffix,
    readonly mediaType: MediaType
  ) {
    super(
      identifier,
      assetFamilyIdentifier,
      code,
      labelCollection,
      MEDIA_LINK_ATTRIBUTE_TYPE,
      valuePerLocale,
      valuePerChannel,
      order,
      is_required
    );

    if (!isValidPrefix(prefix)) {
      throw new InvalidArgumentError('Attribute expects a valid Prefix as prefix');
    }

    if (!isValidSuffix(suffix)) {
      throw new InvalidArgumentError('Attribute expects a valid Suffix as suffix');
    }

    if (!isValidMediaType(mediaType)) {
      throw new InvalidArgumentError('Attribute expects a valid MediaType as mediaType');
    }

    Object.freeze(this);
  }

  public static createFromNormalized(normalizedMediaLinkAttribute: NormalizedMediaLinkAttribute) {
    return new ConcreteMediaLinkAttribute(
      denormalizeAttributeIdentifier(normalizedMediaLinkAttribute.identifier),
      denormalizeAssetFamilyIdentifier(normalizedMediaLinkAttribute.asset_family_identifier),
      denormalizeAttributeCode(normalizedMediaLinkAttribute.code),
      denormalizeLabelCollection(normalizedMediaLinkAttribute.labels),
      normalizedMediaLinkAttribute.value_per_locale,
      normalizedMediaLinkAttribute.value_per_channel,
      normalizedMediaLinkAttribute.order,
      normalizedMediaLinkAttribute.is_required,
      createPrefixFromNormalized(normalizedMediaLinkAttribute.prefix),
      createSuffixFromNormalized(normalizedMediaLinkAttribute.suffix),
      createMediaTypeFromNormalized(normalizedMediaLinkAttribute.media_type)
    );
  }

  public normalize(): NormalizedMediaLinkAttribute {
    return {
      ...super.normalize(),
      type: MEDIA_LINK_ATTRIBUTE_TYPE,
      prefix: normalizePrefix(this.prefix),
      suffix: normalizeSuffix(this.suffix),
      media_type: normalizeMediaType(this.mediaType),
    };
  }
}

export const denormalize = ConcreteMediaLinkAttribute.createFromNormalized;
