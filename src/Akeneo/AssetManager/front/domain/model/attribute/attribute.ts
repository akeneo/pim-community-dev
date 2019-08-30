import Identifier, {NormalizedAttributeIdentifier} from 'akeneoassetmanager/domain/model/attribute/identifier';
import MinimalAttribute, {
    MinimalConcreteAttribute,
    MinimalNormalizedAttribute,
} from 'akeneoassetmanager/domain/model/attribute/minimal';
import AttributeCode from 'akeneoassetmanager/domain/model/attribute/code';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection from 'akeneoassetmanager/domain/model/label-collection';

/**
 * @api
 */
export interface NormalizedAttribute extends MinimalNormalizedAttribute {
  identifier: NormalizedAttributeIdentifier;
  order: number;
  is_required: boolean;
}

/**
 * @api
 */
export interface Attribute extends MinimalAttribute {
  identifier: Identifier;
  order: number;
  isRequired: boolean;
  equals: (attribute: MinimalAttribute) => boolean;
  getIdentifier: () => Identifier;
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

/**
 * @api
 */
export abstract class ConcreteAttribute extends MinimalConcreteAttribute implements Attribute {
  protected constructor(
    readonly identifier: Identifier,
    assetFamilyIdentifier: AssetFamilyIdentifier,
    code: AttributeCode,
    labelCollection: LabelCollection,
    type: string,
    valuePerLocale: boolean,
    valuePerChannel: boolean,
    readonly order: number,
    readonly isRequired: boolean
  ) {
    super(assetFamilyIdentifier, code, labelCollection, type, valuePerLocale, valuePerChannel);

    if (!(identifier instanceof Identifier)) {
      throw new InvalidArgumentError('Attribute expects an AttributeIdentifier argument');
    }

    if (typeof order !== 'number') {
      throw new InvalidArgumentError('Attribute expects a number as order');
    }
    if (typeof isRequired !== 'boolean') {
      throw new InvalidArgumentError('Attribute expects a boolean as isRequired value');
    }
  }

  public equals(attribute: Attribute): boolean {
    return attribute.getIdentifier().equals(this.identifier);
  }

  public normalize(): NormalizedAttribute {
    return {
      identifier: this.identifier.normalize(),
      ...super.normalize(),
      order: this.order,
      is_required: this.isRequired,
    };
  }

  public getIdentifier(): Identifier {
    return this.identifier;
  }
}

class InvalidArgumentError extends Error {}
