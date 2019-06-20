import AssetFamilyIdentifier, {
  createIdentifier as createAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection, {
  NormalizedLabelCollection,
  createLabelCollection,
} from 'akeneoassetmanager/domain/model/label-collection';
import AttributeCode, {createCode} from 'akeneoassetmanager/domain/model/attribute/code';
import {AssetType, NormalizedAssetType} from 'akeneoassetmanager/domain/model/attribute/type/asset/asset-type';

/**
 * @api
 */
export interface MinimalNormalizedAttribute {
  asset_family_identifier: string;
  type: string;
  code: string;
  labels: NormalizedLabelCollection;
  value_per_locale: boolean;
  value_per_channel: boolean;
}

/**
 * @api
 */
export default interface MinimalAttribute {
  assetFamilyIdentifier: AssetFamilyIdentifier;
  code: AttributeCode;
  labelCollection: LabelCollection;
  type: string;
  valuePerLocale: boolean;
  valuePerChannel: boolean;
  getCode: () => AttributeCode;
  getAssetFamilyIdentifier: () => AssetFamilyIdentifier;
  getType(): string;
  getLabel: (locale: string, fallbackOnCode?: boolean) => string;
  getLabelCollection: () => LabelCollection;
  normalize(): MinimalNormalizedAttribute;
}

class InvalidArgumentError extends Error {}

export const isAssetAttributeType = (attributeType: string) => {
  return ['asset', 'asset_collection'].includes(attributeType);
};

/**
 * @api
 */
export class MinimalConcreteAttribute implements MinimalAttribute {
  protected constructor(
    readonly assetFamilyIdentifier: AssetFamilyIdentifier,
    readonly code: AttributeCode,
    readonly labelCollection: LabelCollection,
    readonly type: string,
    readonly valuePerLocale: boolean,
    readonly valuePerChannel: boolean
  ) {
    if (!(assetFamilyIdentifier instanceof AssetFamilyIdentifier)) {
      throw new InvalidArgumentError('Attribute expects an AssetFamilyIdentifier argument');
    }
    if (!(code instanceof AttributeCode)) {
      throw new InvalidArgumentError('Attribute expects a AttributeCode argument');
    }
    if (!(labelCollection instanceof LabelCollection)) {
      throw new InvalidArgumentError('Attribute expects a LabelCollection argument');
    }
    if (typeof type !== 'string') {
      throw new InvalidArgumentError('Attribute expects a string as attribute type');
    }
    if (typeof valuePerLocale !== 'boolean') {
      throw new InvalidArgumentError('Attribute expects a boolean as valuePerLocale');
    }
    if (typeof valuePerChannel !== 'boolean') {
      throw new InvalidArgumentError('Attribute expects a boolean as valuePerChannel');
    }
  }

  public static createFromNormalized(minimalNormalizedAttribute: MinimalNormalizedAttribute) {
    return new MinimalConcreteAttribute(
      createAssetFamilyIdentifier(minimalNormalizedAttribute.asset_family_identifier),
      createCode(minimalNormalizedAttribute.code),
      createLabelCollection(minimalNormalizedAttribute.labels),
      minimalNormalizedAttribute.type,
      minimalNormalizedAttribute.value_per_locale,
      minimalNormalizedAttribute.value_per_channel
    );
  }

  public getAssetFamilyIdentifier(): AssetFamilyIdentifier {
    return this.assetFamilyIdentifier;
  }

  public getCode(): AttributeCode {
    return this.code;
  }

  public getType(): string {
    return this.type;
  }

  public getLabel(locale: string, fallbackOnCode: boolean = true) {
    if (!this.labelCollection.hasLabel(locale)) {
      return fallbackOnCode ? `[${this.getCode().stringValue()}]` : '';
    }

    return this.labelCollection.getLabel(locale);
  }

  public getLabelCollection(): LabelCollection {
    return this.labelCollection;
  }

  public normalize(): MinimalNormalizedAttribute {
    return {
      asset_family_identifier: this.assetFamilyIdentifier.stringValue(),
      code: this.code.stringValue(),
      type: this.getType(),
      labels: this.labelCollection.normalize(),
      value_per_locale: this.valuePerLocale,
      value_per_channel: this.valuePerChannel,
    };
  }
}

export interface MinimalAssetNormalizedAttribute extends MinimalNormalizedAttribute {
  asset_type: NormalizedAssetType;
}

export class MinimalAssetConcreteAttribute extends MinimalConcreteAttribute {
  protected constructor(
    readonly assetFamilyIdentifier: AssetFamilyIdentifier,
    readonly code: AttributeCode,
    readonly labelCollection: LabelCollection,
    readonly type: string,
    readonly valuePerLocale: boolean,
    readonly valuePerChannel: boolean,
    readonly assetType: AssetType
  ) {
    super(assetFamilyIdentifier, code, labelCollection, type, valuePerLocale, valuePerChannel);

    if (!isAssetAttributeType(type)) {
      throw new InvalidArgumentError('MinimalAssetAttribute type needs to be "asset" or "asset_collection"');
    }

    if (!(assetType instanceof AssetType)) {
      throw new InvalidArgumentError('Attribute expects a AssetType argument');
    }
  }

  public static createFromNormalized(minimalNormalizedAttribute: MinimalAssetNormalizedAttribute) {
    return new MinimalAssetConcreteAttribute(
      createAssetFamilyIdentifier(minimalNormalizedAttribute.asset_family_identifier),
      createCode(minimalNormalizedAttribute.code),
      createLabelCollection(minimalNormalizedAttribute.labels),
      minimalNormalizedAttribute.type,
      minimalNormalizedAttribute.value_per_locale,
      minimalNormalizedAttribute.value_per_channel,
      AssetType.createFromNormalized(minimalNormalizedAttribute.asset_type)
    );
  }

  public normalize(): MinimalAssetNormalizedAttribute {
    return {
      ...super.normalize(),
      asset_type: this.assetType.normalize(),
    };
  }
}

export const denormalizeMinimalAttribute = (normalizedAttribute: MinimalNormalizedAttribute) => {
  if (isAssetAttributeType(normalizedAttribute.type)) {
    return MinimalAssetConcreteAttribute.createFromNormalized(normalizedAttribute as MinimalAssetNormalizedAttribute);
  }

  return MinimalConcreteAttribute.createFromNormalized(normalizedAttribute);
};
