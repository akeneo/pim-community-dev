import LabelCollection, {
  NormalizedLabelCollection,
  createLabelCollection,
} from 'akeneoreferenceentity/domain/model/label-collection';
import Identifier, {
  NormalizedIdentifier,
  createIdentifier,
} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import File, {NormalizedFile, denormalizeFile} from 'akeneoreferenceentity/domain/model/file';

export interface NormalizedReferenceEntityListItem {
  identifier: NormalizedIdentifier;
  labels: NormalizedLabelCollection;
  image: NormalizedFile;
}

export default interface ReferenceEntityListItem {
  getIdentifier: () => Identifier;
  getLabel: (locale: string, fallbackOnCode?: boolean) => string;
  getImage: () => File;
  equals: (referenceEntityListItem: ReferenceEntityListItem) => boolean;
  normalize: () => NormalizedReferenceEntityListItem;
}
class InvalidArgumentError extends Error {}

class ReferenceEntityListItemImplementation implements ReferenceEntityListItem {
  private constructor(private identifier: Identifier, private labelCollection: LabelCollection, private image: File) {
    if (!(identifier instanceof Identifier)) {
      throw new InvalidArgumentError('ReferenceEntityListItem expects an Identifier as identifier argument');
    }

    if (!(labelCollection instanceof LabelCollection)) {
      throw new InvalidArgumentError('ReferenceEntityListItem expects a LabelCollection as labelCollection argument');
    }

    if (!(image instanceof File)) {
      throw new InvalidArgumentError('ReferenceEntityListItem expects a File as image argument');
    }

    Object.freeze(this);
  }

  public static create(identifier: Identifier, labelCollection: LabelCollection, image: File): ReferenceEntityListItem {
    return new ReferenceEntityListItemImplementation(identifier, labelCollection, image);
  }

  public static createEmpty(): ReferenceEntityListItem {
    return new ReferenceEntityListItemImplementation(
      createIdentifier(''),
      createLabelCollection({}),
      denormalizeFile(null)
    );
  }

  public static createFromNormalized(
    normalizedReferenceEntity: NormalizedReferenceEntityListItem
  ): ReferenceEntityListItem {
    const identifier = createIdentifier(normalizedReferenceEntity.identifier);
    const labelCollection = createLabelCollection(normalizedReferenceEntity.labels);
    const image = denormalizeFile(normalizedReferenceEntity.image);

    return ReferenceEntityListItemImplementation.create(identifier, labelCollection, image);
  }

  public getIdentifier(): Identifier {
    return this.identifier;
  }

  public getLabel(locale: string, fallbackOnCode: boolean = true) {
    if (!this.labelCollection.hasLabel(locale)) {
      return fallbackOnCode ? `[${this.getIdentifier().stringValue()}]` : '';
    }

    return this.labelCollection.getLabel(locale);
  }

  public getImage(): File {
    return this.image;
  }

  public getLabelCollection(): LabelCollection {
    return this.labelCollection;
  }

  public equals(referenceEntityListItem: ReferenceEntityListItem): boolean {
    return referenceEntityListItem.getIdentifier().equals(this.identifier);
  }

  public normalize(): NormalizedReferenceEntityListItem {
    return {
      identifier: this.getIdentifier().stringValue(),
      labels: this.getLabelCollection().normalize(),
      image: this.getImage().normalize(),
    };
  }
}

export const createReferenceEntityListItem = ReferenceEntityListItemImplementation.create;
export const createEmptyReferenceEntityListItem = ReferenceEntityListItemImplementation.createEmpty;
export const denormalizeReferenceEntityListItem = ReferenceEntityListItemImplementation.createFromNormalized;
