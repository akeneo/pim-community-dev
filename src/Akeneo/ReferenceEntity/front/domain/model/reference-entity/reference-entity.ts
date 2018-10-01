import Identifier, {createIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import LabelCollection, {
  NormalizedLabelCollection,
  createLabelCollection,
} from 'akeneoreferenceentity/domain/model/label-collection';
import File, {NormalizedFile, denormalizeFile} from 'akeneoreferenceentity/domain/model/file';

export interface NormalizedReferenceEntity {
  identifier: string;
  code: string;
  labels: NormalizedLabelCollection;
  image: NormalizedFile;
}

export default interface ReferenceEntity {
  getIdentifier: () => Identifier;
  getLabel: (locale: string, defaultValue?: boolean) => string;
  getLabelCollection: () => LabelCollection;
  getImage: () => File;
  equals: (referenceEntity: ReferenceEntity) => boolean;
  normalize: () => NormalizedReferenceEntity;
}
class InvalidArgumentError extends Error {}

class ReferenceEntityImplementation implements ReferenceEntity {
  private constructor(private identifier: Identifier, private labelCollection: LabelCollection, private image: File) {
    if (!(identifier instanceof Identifier)) {
      throw new InvalidArgumentError('ReferenceEntity expect an ReferenceEntityIdentifier as identifier argument');
    }
    if (!(labelCollection instanceof LabelCollection)) {
      throw new InvalidArgumentError('ReferenceEntity expect a LabelCollection as labelCollection argument');
    }
    if (!(image instanceof File)) {
      throw new InvalidArgumentError('ReferenceEntity expect a File as image argument');
    }

    Object.freeze(this);
  }

  public static create(identifier: Identifier, labelCollection: LabelCollection, image: File): ReferenceEntity {
    return new ReferenceEntityImplementation(identifier, labelCollection, image);
  }

  public static createFromNormalized(normalizedReferenceEntity: NormalizedReferenceEntity): ReferenceEntity {
    const identifier = createIdentifier(normalizedReferenceEntity.identifier);
    const labelCollection = createLabelCollection(normalizedReferenceEntity.labels);
    const image = denormalizeFile(normalizedReferenceEntity.image);

    return ReferenceEntityImplementation.create(identifier, labelCollection, image);
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

  public equals(referenceEntity: ReferenceEntity): boolean {
    return referenceEntity.getIdentifier().equals(this.identifier);
  }

  public normalize(): NormalizedReferenceEntity {
    return {
      identifier: this.getIdentifier().stringValue(),
      code: this.getIdentifier().stringValue(),
      labels: this.getLabelCollection().normalize(),
      image: this.getImage().normalize(),
    };
  }
}

export const createReferenceEntity = ReferenceEntityImplementation.create;
export const denormalizeReferenceEntity = ReferenceEntityImplementation.createFromNormalized;
