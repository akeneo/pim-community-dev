import Identifier, {NormalizedAttributeIdentifier} from 'akeneoreferenceentity/domain/model/attribute/identifier';
import MinimalAttribute, {
  MinimalNormalizedAttribute,
  MinimalConcreteAttribute,
} from 'akeneoreferenceentity/domain/model/attribute/minimal';
import AttributeCode from 'akeneoreferenceentity/domain/model/attribute/code';
import ReferenceEntityIdentifier from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import LabelCollection from 'akeneoreferenceentity/domain/model/label-collection';

export interface NormalizedAttribute extends MinimalNormalizedAttribute {
  identifier: NormalizedAttributeIdentifier;
  order: number;
  is_required: boolean;
}

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

export abstract class ConcreteAttribute extends MinimalConcreteAttribute implements Attribute {
  protected constructor(
    readonly identifier: Identifier,
    referenceEntityIdentifier: ReferenceEntityIdentifier,
    code: AttributeCode,
    labelCollection: LabelCollection,
    type: string,
    valuePerLocale: boolean,
    valuePerChannel: boolean,
    readonly order: number,
    readonly isRequired: boolean
  ) {
    super(referenceEntityIdentifier, code, labelCollection, type, valuePerLocale, valuePerChannel);

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
