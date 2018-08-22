import MinimalAttribute, {
  MinimalNormalizedAttribute,
  MinimalConcreteAttribute,
  AttributeType,
} from 'akeneoenrichedentity/domain/model/attribute/minimal';
import Identifier from 'akeneoenrichedentity/domain/model/attribute/identifier';
import AttributeCode from 'akeneoenrichedentity/domain/model/attribute/code';
import EnrichedEntityIdentifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection from 'akeneoenrichedentity/domain/model/label-collection';

export interface CommonNormalizedAttribute extends MinimalNormalizedAttribute {
  order: number;
  required: boolean;
}

export interface CommonAttribute extends MinimalAttribute {
  order: number;
  required: boolean;
  normalize(): CommonNormalizedAttribute;
}

export abstract class CommonConcreteAttribute extends MinimalConcreteAttribute implements CommonAttribute {
  protected constructor(
    identifier: Identifier,
    enrichedEntityIdentifier: EnrichedEntityIdentifier,
    code: AttributeCode,
    labelCollection: LabelCollection,
    type: AttributeType,
    valuePerLocale: boolean,
    valuePerChannel: boolean,
    readonly order: number,
    readonly required: boolean
  ) {
    super(identifier, enrichedEntityIdentifier, code, labelCollection, type, valuePerLocale, valuePerChannel);

    if (typeof order !== 'number') {
      throw new InvalidArgumentError('Attribute expect a number as order');
    }
    if (typeof required !== 'boolean') {
      throw new InvalidArgumentError('Attribute expect a boolean as required value');
    }
  }

  public normalize(): CommonNormalizedAttribute {
    return {
      ...super.normalize(),
      order: this.order,
      required: this.required,
    };
  }
}

class InvalidArgumentError extends Error {}
