import AttributeIdentifier, {
  denormalizeAttributeIdentifier,
} from 'akeneoassetmanager/domain/model/attribute/identifier';
import AssetFamilyIdentifier, {
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection, {denormalizeLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import AttributeCode, {denormalizeAttributeCode} from 'akeneoassetmanager/domain/model/attribute/code';
import {NormalizedAttribute, Attribute, ConcreteAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {
  AssetType,
  createAssetTypeFromNormalized,
} from 'akeneoassetmanager/domain/model/attribute/type/asset/asset-type';

export interface NormalizedAssetAttribute extends NormalizedAttribute {
  type: 'asset';
  asset_type: AssetType;
}

export type NormalizedAssetAdditionalProperty = AssetType;

export type AssetAdditionalProperty = AssetType;

export interface AssetAttribute extends Attribute {
  assetType: AssetType;
  normalize(): NormalizedAssetAttribute;
  getAssetType(): AssetType;
}

export class InvalidArgumentError extends Error {}

export class ConcreteAssetAttribute extends ConcreteAttribute implements AssetAttribute {
  private constructor(
    identifier: AttributeIdentifier,
    assetFamilyIdentifier: AssetFamilyIdentifier,
    code: AttributeCode,
    labelCollection: LabelCollection,
    valuePerLocale: boolean,
    valuePerChannel: boolean,
    order: number,
    is_required: boolean,
    readonly assetType: AssetType
  ) {
    super(
      identifier,
      assetFamilyIdentifier,
      code,
      labelCollection,
      'asset',
      valuePerLocale,
      valuePerChannel,
      order,
      is_required
    );

    Object.freeze(this);
  }

  public static createFromNormalized(normalizedAssetAttribute: NormalizedAssetAttribute) {
    return new ConcreteAssetAttribute(
      denormalizeAttributeIdentifier(normalizedAssetAttribute.identifier),
      denormalizeAssetFamilyIdentifier(normalizedAssetAttribute.asset_family_identifier),
      denormalizeAttributeCode(normalizedAssetAttribute.code),
      denormalizeLabelCollection(normalizedAssetAttribute.labels),
      normalizedAssetAttribute.value_per_locale,
      normalizedAssetAttribute.value_per_channel,
      normalizedAssetAttribute.order,
      normalizedAssetAttribute.is_required,
      createAssetTypeFromNormalized(normalizedAssetAttribute.asset_type)
    );
  }

  getAssetType(): AssetType {
    return this.assetType;
  }

  public normalize(): NormalizedAssetAttribute {
    return {
      ...super.normalize(),
      type: 'asset',
      asset_type: this.assetType,
    };
  }
}

export const denormalize = ConcreteAssetAttribute.createFromNormalized;
