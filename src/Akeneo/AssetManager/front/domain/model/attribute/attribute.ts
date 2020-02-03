import AttributeIdentifier, {attributeidentifiersAreEqual} from 'akeneoassetmanager/domain/model/attribute/identifier';
import MinimalAttribute, {
  MinimalConcreteAttribute,
  MinimalNormalizedAttribute,
} from 'akeneoassetmanager/domain/model/attribute/minimal';
import AttributeCode from 'akeneoassetmanager/domain/model/attribute/code';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection, {denormalizeLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';

/**
 * @api
 */
export interface NormalizedAttribute extends MinimalNormalizedAttribute {
  identifier: AttributeIdentifier;
  order: number;
  is_required: boolean;
  is_read_only: boolean;
}

/**
 * @api
 */
export interface Attribute extends MinimalAttribute {
  identifier: AttributeIdentifier;
  order: number;
  isRequired: boolean;
  isReadOnly: boolean;
  equals: (attribute: MinimalAttribute) => boolean;
  getIdentifier: () => AttributeIdentifier;
  normalize(): NormalizedAttribute;
}

export interface NormalizableAdditionalProperty {
  normalize(): any;
}

export const wrapNormalizableAdditionalProperty = <NormalizedAdditionalProperty>(
  normalizedAdditionalProperty: NormalizedAdditionalProperty
): NormalizableAdditionalProperty => {
  return {
    normalize: (): NormalizedAdditionalProperty => {
      return normalizedAdditionalProperty;
    },
  };
};

export const denormalizeAttribute = (attribute: any): NormalizedAttribute => {
  return {
    ...attribute,
    labels: denormalizeLabelCollection(attribute.labels),
  };
};

/**
 * @api
 */
export abstract class ConcreteAttribute extends MinimalConcreteAttribute implements Attribute {
  protected constructor(
    readonly identifier: AttributeIdentifier,
    assetFamilyIdentifier: AssetFamilyIdentifier,
    code: AttributeCode,
    labelCollection: LabelCollection,
    type: string,
    valuePerLocale: boolean,
    valuePerChannel: boolean,
    readonly order: number,
    readonly isRequired: boolean,
    readonly isReadOnly: boolean
  ) {
    super(assetFamilyIdentifier, code, labelCollection, type, valuePerLocale, valuePerChannel);

    if (typeof order !== 'number') {
      throw new InvalidArgumentError('Attribute expects a number as order');
    }
    if (typeof isRequired !== 'boolean') {
      throw new InvalidArgumentError('Attribute expects a boolean as isRequired value');
    }
    if (typeof isReadOnly !== 'boolean') {
      throw new InvalidArgumentError('Attribute expects a boolean as isReadOnly value');
    }
  }

  public equals(attribute: Attribute): boolean {
    return attributeidentifiersAreEqual(attribute.getIdentifier(), this.identifier);
  }

  public normalize(): NormalizedAttribute {
    return {
      identifier: this.identifier,
      ...super.normalize(),
      order: this.order,
      is_required: this.isRequired,
      is_read_only: this.isReadOnly,
    };
  }

  public getIdentifier(): AttributeIdentifier {
    return this.identifier;
  }
}

class InvalidArgumentError extends Error {}
