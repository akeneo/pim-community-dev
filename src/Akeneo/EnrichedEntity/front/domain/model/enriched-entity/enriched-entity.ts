import Identifier from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection from 'akeneoenrichedentity/domain/model/label-collection';

export default interface EnrichedEntity {
  getIdentifier: () => Identifier;
  getLabel: (locale: string) => string;
  equals: (enrichedEntity: EnrichedEntity) => boolean;
};
class InvalidArgumentError extends Error {}

class EnrichedEntityImplementation implements EnrichedEntity {
  private constructor(private identifier: Identifier, private labelCollection: LabelCollection) {
    if (!(identifier instanceof Identifier)) {
      throw new InvalidArgumentError('EnrichedEntity expect an EnrichedEntityIdentifier as first argument');
    }
    if (!(labelCollection instanceof LabelCollection)) {
      throw new InvalidArgumentError('EnrichedEntity expect a LabelCollection as second argument');
    }

    Object.freeze(this);
  }

  public static create(identifier: Identifier, labelCollection: LabelCollection): EnrichedEntity {
    return new EnrichedEntityImplementation(identifier, labelCollection);
  }

  public getIdentifier(): Identifier {
    return this.identifier;
  }

  public getLabel(locale: string) {
    return this.labelCollection.hasLabel(locale)
      ? this.labelCollection.getLabel(locale)
      : `[${this.getIdentifier().stringValue()}]`;
  }

  public equals(enrichedEntity: EnrichedEntity): boolean {
    return enrichedEntity.getIdentifier().equals(this.identifier);
  }
}

export const createEnrichedEntity = EnrichedEntityImplementation.create;
