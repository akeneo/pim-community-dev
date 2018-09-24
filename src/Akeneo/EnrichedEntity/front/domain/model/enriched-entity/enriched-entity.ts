import Identifier, {createIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';
import LabelCollection, {
  NormalizedLabelCollection,
  createLabelCollection,
} from 'akeneoenrichedentity/domain/model/label-collection';
import File, {NormalizedFile, denormalizeFile} from 'akeneoenrichedentity/domain/model/file';

export interface NormalizedEnrichedEntity {
  identifier: string;
  code: string;
  labels: NormalizedLabelCollection;
  image: NormalizedFile;
}

export default interface EnrichedEntity {
  getIdentifier: () => Identifier;
  getLabel: (locale: string, defaultValue?: boolean) => string;
  getLabelCollection: () => LabelCollection;
  getImage: () => File;
  equals: (enrichedEntity: EnrichedEntity) => boolean;
  normalize: () => NormalizedEnrichedEntity;
}
class InvalidArgumentError extends Error {}

class EnrichedEntityImplementation implements EnrichedEntity {
  private constructor(private identifier: Identifier, private labelCollection: LabelCollection, private image: File) {
    if (!(identifier instanceof Identifier)) {
      throw new InvalidArgumentError('EnrichedEntity expect an EnrichedEntityIdentifier as identifier argument');
    }
    if (!(labelCollection instanceof LabelCollection)) {
      throw new InvalidArgumentError('EnrichedEntity expect a LabelCollection as labelCollection argument');
    }
    if (!(image instanceof File)) {
      throw new InvalidArgumentError('EnrichedEntity expect a File as image argument');
    }

    Object.freeze(this);
  }

  public static create(identifier: Identifier, labelCollection: LabelCollection, image: File): EnrichedEntity {
    return new EnrichedEntityImplementation(identifier, labelCollection, image);
  }

  public static createFromNormalized(normalizedEnrichedEntity: NormalizedEnrichedEntity): EnrichedEntity {
    const identifier = createIdentifier(normalizedEnrichedEntity.identifier);
    const labelCollection = createLabelCollection(normalizedEnrichedEntity.labels);
    const image = denormalizeFile(normalizedEnrichedEntity.image);

    return EnrichedEntityImplementation.create(identifier, labelCollection, image);
  }

  public getIdentifier(): Identifier {
    return this.identifier;
  }

  public getLabel(locale: string, defaultValue: boolean = true) {
    if (!this.labelCollection.hasLabel(locale)) {
      return defaultValue ? `[${this.getIdentifier().stringValue()}]` : '';
    }

    return this.labelCollection.getLabel(locale);
  }

  public getLabelCollection(): LabelCollection {
    return this.labelCollection;
  }

  public getImage(): File {
    return this.image;
  }

  public equals(enrichedEntity: EnrichedEntity): boolean {
    return enrichedEntity.getIdentifier().equals(this.identifier);
  }

  public normalize(): NormalizedEnrichedEntity {
    return {
      identifier: this.getIdentifier().stringValue(),
      code: this.getIdentifier().stringValue(),
      labels: this.getLabelCollection().normalize(),
      image: this.getImage().normalize(),
    };
  }
}

export const createEnrichedEntity = EnrichedEntityImplementation.create;
export const denormalizeEnrichedEntity = EnrichedEntityImplementation.createFromNormalized;
