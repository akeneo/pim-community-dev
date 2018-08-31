import Identifier, {NormalizedAttributeIdentifier} from 'akeneoenrichedentity/domain/model/attribute/identifier';
import MinimalAttribute, {
  MinimalNormalizedAttribute,
  MinimalConcreteAttribute,
  AttributeType,
} from 'akeneoenrichedentity/domain/model/attribute/minimal';
import AttributeCode from 'akeneoenrichedentity/domain/model/attribute/code';
import EnrichedEntityIdentifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection from 'akeneoenrichedentity/domain/model/label-collection';

export interface CommonNormalizedAttribute extends MinimalNormalizedAttribute {
  identifier: NormalizedAttributeIdentifier;
  order: number;
  is_required: boolean;
}

export interface CommonAttribute extends MinimalAttribute {
  identifier: Identifier;
  order: number;
  isRequired: boolean;
  equals: (attribute: MinimalAttribute) => boolean;
  getIdentifier: () => Identifier;
  normalize(): CommonNormalizedAttribute;
}

export abstract class CommonConcreteAttribute extends MinimalConcreteAttribute implements CommonAttribute {
  protected constructor(
    readonly identifier: Identifier,
    enrichedEntityIdentifier: EnrichedEntityIdentifier,
    code: AttributeCode,
    labelCollection: LabelCollection,
    type: AttributeType,
    valuePerLocale: boolean,
    valuePerChannel: boolean,
    readonly order: number,
    readonly isRequired: boolean
  ) {
    super(enrichedEntityIdentifier, code, labelCollection, type, valuePerLocale, valuePerChannel);

    if (!(identifier instanceof Identifier)) {
      throw new InvalidArgumentError('Attribute expect an AttributeIdentifier argument');
    }

    if (typeof order !== 'number') {
      throw new InvalidArgumentError('Attribute expect a number as order');
    }
    if (typeof isRequired !== 'boolean') {
      throw new InvalidArgumentError('Attribute expect a boolean as isRequired value');
    }
  }

  public equals(attribute: CommonAttribute): boolean {
    return attribute.getIdentifier().equals(this.identifier);
  }

  public normalize(): CommonNormalizedAttribute {
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
