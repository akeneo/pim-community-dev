import AttributeIdentifier, {
  denormalizeAttributeIdentifier,
} from 'akeneoassetmanager/domain/model/attribute/identifier';
import AssetFamilyIdentifier, {
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection, {createLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import AttributeCode, {denormalizeAttributeCode} from 'akeneoassetmanager/domain/model/attribute/code';
import {NormalizedAttribute, Attribute, ConcreteAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {AssetType, NormalizedAssetType} from 'akeneoassetmanager/domain/model/attribute/type/asset/asset-type';

export interface NormalizedAssetCollectionAttribute extends NormalizedAttribute {
  type: 'asset_collection';
  asset_type: NormalizedAssetType;
}

export type NormalizedAssetAdditionalProperty = NormalizedAssetType;

export type AssetAdditionalProperty = AssetType;

export interface AssetCollectionAttribute extends Attribute {
  assetType: AssetType;
  normalize(): NormalizedAssetCollectionAttribute;
  getAssetType(): AssetType;
}

export class InvalidArgumentError extends Error {}

export class ConcreteAssetCollectionAttribute extends ConcreteAttribute implements AssetCollectionAttribute {
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
      'asset_collection',
      valuePerLocale,
      valuePerChannel,
      order,
      is_required
    );

    if (!(assetType instanceof AssetType)) {
      throw new InvalidArgumentError('Attribute expects a AssetType as assetType');
    }

    Object.freeze(this);
  }

  public static createFromNormalized(normalizedAssetCollectionAttribute: NormalizedAssetCollectionAttribute) {
    return new ConcreteAssetCollectionAttribute(
      denormalizeAttributeIdentifier(normalizedAssetCollectionAttribute.identifier),
      denormalizeAssetFamilyIdentifier(normalizedAssetCollectionAttribute.asset_family_identifier),
      denormalizeAttributeCode(normalizedAssetCollectionAttribute.code),
      createLabelCollection(normalizedAssetCollectionAttribute.labels),
      normalizedAssetCollectionAttribute.value_per_locale,
      normalizedAssetCollectionAttribute.value_per_channel,
      normalizedAssetCollectionAttribute.order,
      normalizedAssetCollectionAttribute.is_required,
      AssetType.createFromNormalized(normalizedAssetCollectionAttribute.asset_type)
    );
  }

  getAssetType(): AssetType {
    return this.assetType;
  }

  public normalize(): NormalizedAssetCollectionAttribute {
    return {
      ...super.normalize(),
      type: 'asset_collection',
      asset_type: this.assetType.normalize(),
    };
  }
}

export const denormalize = ConcreteAssetCollectionAttribute.createFromNormalized;
