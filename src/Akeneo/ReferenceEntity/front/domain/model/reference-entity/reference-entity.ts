import Identifier, {createIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import LabelCollection, {
  NormalizedLabelCollection,
  createLabelCollection,
} from 'akeneoreferenceentity/domain/model/label-collection';
import File, {NormalizedFile, denormalizeFile} from 'akeneoreferenceentity/domain/model/file';
import AttributeIdentifier, {
  createIdentifier as createAttributeIdentifier,
  NormalizedAttributeIdentifier,
} from 'akeneoreferenceentity/domain/model/attribute/identifier';

export interface NormalizedReferenceEntity {
  identifier: string;
  code: string;
  labels: NormalizedLabelCollection;
  image: NormalizedFile;
  attribute_as_label: NormalizedAttributeIdentifier;
  attribute_as_image: NormalizedAttributeIdentifier;
}

export default interface ReferenceEntity {
  getIdentifier: () => Identifier;
  getLabel: (locale: string, fallbackOnCode?: boolean) => string;
  getLabelCollection: () => LabelCollection;
  getImage: () => File;
  getAttributeAsLabel: () => AttributeIdentifier;
  getAttributeAsImage: () => AttributeIdentifier;
  equals: (referenceEntity: ReferenceEntity) => boolean;
  normalize: () => NormalizedReferenceEntity;
}
class InvalidArgumentError extends Error {}

class ReferenceEntityImplementation implements ReferenceEntity {
  private constructor(
    private identifier: Identifier,
    private labelCollection: LabelCollection,
    private image: File,
    private attributeAsLabel: AttributeIdentifier,
    private attributeAsImage: AttributeIdentifier
  ) {
    if (!(identifier instanceof Identifier)) {
      throw new InvalidArgumentError('ReferenceEntity expects an ReferenceEntityIdentifier as identifier argument');
    }
    if (!(labelCollection instanceof LabelCollection)) {
      throw new InvalidArgumentError('ReferenceEntity expects a LabelCollection as labelCollection argument');
    }
    if (!(image instanceof File)) {
      throw new InvalidArgumentError('ReferenceEntity expects a File as image argument');
    }
    if (!(attributeAsLabel instanceof AttributeIdentifier)) {
      throw new InvalidArgumentError('ReferenceEntity expects a AttributeIdentifier as attributeAsLabel argument');
    }
    if (!(attributeAsImage instanceof AttributeIdentifier)) {
      throw new InvalidArgumentError('ReferenceEntity expects a AttributeIdentifier as attributeAsImage argument');
    }

    Object.freeze(this);
  }

  public static create(
    identifier: Identifier,
    labelCollection: LabelCollection,
    image: File,
    attributeAsLabel: AttributeIdentifier,
    attributeAsImage: AttributeIdentifier
  ): ReferenceEntity {
    return new ReferenceEntityImplementation(identifier, labelCollection, image, attributeAsLabel, attributeAsImage);
  }

  public static createFromNormalized(normalizedReferenceEntity: NormalizedReferenceEntity): ReferenceEntity {
    const identifier = createIdentifier(normalizedReferenceEntity.identifier);
    const labelCollection = createLabelCollection(normalizedReferenceEntity.labels);
    const image = denormalizeFile(normalizedReferenceEntity.image);
    const attributeAsLabel = createAttributeIdentifier(normalizedReferenceEntity.attribute_as_label);
    const attributeAsImage = createAttributeIdentifier(normalizedReferenceEntity.attribute_as_image);

    return ReferenceEntityImplementation.create(identifier, labelCollection, image, attributeAsLabel, attributeAsImage);
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

  public getLabelCollection(): LabelCollection {
    return this.labelCollection;
  }

  public getImage(): File {
    return this.image;
  }

  public getAttributeAsLabel(): AttributeIdentifier {
    return this.attributeAsLabel;
  }

  public getAttributeAsImage(): AttributeIdentifier {
    return this.attributeAsImage;
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
      attribute_as_label: this.getAttributeAsLabel().normalize(),
      attribute_as_image: this.getAttributeAsImage().normalize(),
    };
  }
}

export const createReferenceEntity = ReferenceEntityImplementation.create;
export const denormalizeReferenceEntity = ReferenceEntityImplementation.createFromNormalized;
