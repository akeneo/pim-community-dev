import Identifier, {createIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection, {
  RawLabelCollection,
  createLabelCollection,
} from 'akeneoenrichedentity/domain/model/label-collection';

export interface NormalizedEnrichedEntity {
  identifier: string;
  labels: RawLabelCollection;
}

export default interface EnrichedEntity {
  getIdentifier: () => Identifier;
  getLabel: (locale: string) => string;
  getLabelCollection: () => LabelCollection;
  equals: (enrichedEntity: EnrichedEntity) => boolean;
  normalize: () => NormalizedEnrichedEntity;
}
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

  public static createFormNormalized(normalizedEnrichedEntity: NormalizedEnrichedEntity): EnrichedEntity {
    const identifier = createIdentifier(normalizedEnrichedEntity.identifier);
    const labelCollection = createLabelCollection(normalizedEnrichedEntity.labels);

    return EnrichedEntityImplementation.create(identifier, labelCollection);
  }

  public getIdentifier(): Identifier {
    return this.identifier;
  }

  public getLabel(locale: string) {
    return this.labelCollection.hasLabel(locale)
      ? this.labelCollection.getLabel(locale)
      : `[${this.getIdentifier().stringValue()}]`;
  }

  public getLabelCollection(): LabelCollection {
    return this.labelCollection;
  }

  public equals(enrichedEntity: EnrichedEntity): boolean {
    return enrichedEntity.getIdentifier().equals(this.identifier);
  }

  public normalize(): NormalizedEnrichedEntity {
    return {
      identifier: this.getIdentifier().stringValue(),
      labels: this.getLabelCollection().getLabels(),
    };
  }
}

export const createEnrichedEntity = EnrichedEntityImplementation.create;
export const denormalizeEnrichedEntity = EnrichedEntityImplementation.createFormNormalized;
