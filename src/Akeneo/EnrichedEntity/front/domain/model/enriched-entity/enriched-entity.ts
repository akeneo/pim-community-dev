import Identifier, {createIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection, {
  RawLabelCollection,
  createLabelCollection,
} from 'akeneoenrichedentity/domain/model/label-collection';
import Image from 'akeneoenrichedentity/domain/model/image';

export interface NormalizedEnrichedEntity {
  identifier: string;
  labels: RawLabelCollection;
  image: Image | null;
}

export default interface EnrichedEntity {
  getIdentifier: () => Identifier;
  getLabel: (locale: string) => string;
  getLabelCollection: () => LabelCollection;
  getImage: () => Image | null;
  equals: (enrichedEntity: EnrichedEntity) => boolean;
  normalize: () => NormalizedEnrichedEntity;
}
class InvalidArgumentError extends Error {}

class EnrichedEntityImplementation implements EnrichedEntity {
  private constructor(
    private identifier: Identifier,
    private labelCollection: LabelCollection,
    private image: Image | null
  ) {
    if (!(identifier instanceof Identifier)) {
      throw new InvalidArgumentError('EnrichedEntity expect an EnrichedEntityIdentifier as first argument');
    }
    if (!(labelCollection instanceof LabelCollection)) {
      throw new InvalidArgumentError('EnrichedEntity expect a LabelCollection as second argument');
    }

    Object.freeze(this);
  }

  public static create(
    identifier: Identifier,
    labelCollection: LabelCollection,
    image: Image | null = null
  ): EnrichedEntity {
    return new EnrichedEntityImplementation(identifier, labelCollection, image);
  }

  public static createFromNormalized(normalizedEnrichedEntity: NormalizedEnrichedEntity): EnrichedEntity {
    const identifier = createIdentifier(normalizedEnrichedEntity.identifier);
    const labelCollection = createLabelCollection(normalizedEnrichedEntity.labels);

    return EnrichedEntityImplementation.create(identifier, labelCollection, normalizedEnrichedEntity.image);
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

  public getImage(): Image | null {
    return this.image;
  }

  public equals(enrichedEntity: EnrichedEntity): boolean {
    return enrichedEntity.getIdentifier().equals(this.identifier);
  }

  public normalize(): NormalizedEnrichedEntity {
    return {
      identifier: this.getIdentifier().stringValue(),
      labels: this.getLabelCollection().getLabels(),
      image: this.getImage(),
    };
  }
}

export const createEnrichedEntity = EnrichedEntityImplementation.create;
export const denormalizeEnrichedEntity = EnrichedEntityImplementation.createFromNormalized;
