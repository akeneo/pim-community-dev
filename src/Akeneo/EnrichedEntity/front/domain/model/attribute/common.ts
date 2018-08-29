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
  is_required: boolean;
}

export interface CommonAttribute extends MinimalAttribute {
  order: number;
  isRequired: boolean;
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
    readonly isRequired: boolean
  ) {
    super(identifier, enrichedEntityIdentifier, code, labelCollection, type, valuePerLocale, valuePerChannel);

    if (typeof order !== 'number') {
      throw new InvalidArgumentError('Attribute expect a number as order');
    }
    if (typeof isRequired !== 'boolean') {
      throw new InvalidArgumentError('Attribute expect a boolean as isRequired value');
    }
  }

  public normalize(): CommonNormalizedAttribute {
    return {
      ...super.normalize(),
      order: this.order,
      is_required: this.isRequired,
    };
  }
}

class InvalidArgumentError extends Error {}
