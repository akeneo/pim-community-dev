import Identifier, {createIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection, {
  NormalizedLabelCollection,
  createLabelCollection,
} from 'akeneoassetmanager/domain/model/label-collection';
import File, {NormalizedFile, denormalizeFile} from 'akeneoassetmanager/domain/model/file';
import AttributeIdentifier, {
  createIdentifier as createAttributeIdentifier,
  NormalizedAttributeIdentifier,
} from 'akeneoassetmanager/domain/model/attribute/identifier';

export interface NormalizedAssetFamily {
  identifier: string;
  code: string;
  labels: NormalizedLabelCollection;
  image: NormalizedFile;
  attribute_as_label: NormalizedAttributeIdentifier;
  attribute_as_image: NormalizedAttributeIdentifier;
}

export default interface AssetFamily {
  getIdentifier: () => Identifier;
  getLabel: (locale: string, fallbackOnCode?: boolean) => string;
  getLabelCollection: () => LabelCollection;
  getImage: () => File;
  getAttributeAsLabel: () => AttributeIdentifier;
  getAttributeAsImage: () => AttributeIdentifier;
  equals: (assetFamily: AssetFamily) => boolean;
  normalize: () => NormalizedAssetFamily;
}
class InvalidArgumentError extends Error {}

class AssetFamilyImplementation implements AssetFamily {
  private constructor(
    private identifier: Identifier,
    private labelCollection: LabelCollection,
    private image: File,
    private attributeAsLabel: AttributeIdentifier,
    private attributeAsImage: AttributeIdentifier
  ) {
    if (!(identifier instanceof Identifier)) {
      throw new InvalidArgumentError('AssetFamily expects an AssetFamilyIdentifier as identifier argument');
    }
    if (!(labelCollection instanceof LabelCollection)) {
      throw new InvalidArgumentError('AssetFamily expects a LabelCollection as labelCollection argument');
    }
    if (!(image instanceof File)) {
      throw new InvalidArgumentError('AssetFamily expects a File as image argument');
    }
    if (!(attributeAsLabel instanceof AttributeIdentifier)) {
      throw new InvalidArgumentError('AssetFamily expects a AttributeIdentifier as attributeAsLabel argument');
    }
    if (!(attributeAsImage instanceof AttributeIdentifier)) {
      throw new InvalidArgumentError('AssetFamily expects a AttributeIdentifier as attributeAsImage argument');
    }

    Object.freeze(this);
  }

  public static create(
    identifier: Identifier,
    labelCollection: LabelCollection,
    image: File,
    attributeAsLabel: AttributeIdentifier,
    attributeAsImage: AttributeIdentifier
  ): AssetFamily {
    return new AssetFamilyImplementation(identifier, labelCollection, image, attributeAsLabel, attributeAsImage);
  }

  public static createFromNormalized(normalizedAssetFamily: NormalizedAssetFamily): AssetFamily {
    const identifier = createIdentifier(normalizedAssetFamily.identifier);
    const labelCollection = createLabelCollection(normalizedAssetFamily.labels);
    const image = denormalizeFile(normalizedAssetFamily.image);
    const attributeAsLabel = createAttributeIdentifier(normalizedAssetFamily.attribute_as_label);
    const attributeAsImage = createAttributeIdentifier(normalizedAssetFamily.attribute_as_image);

    return AssetFamilyImplementation.create(identifier, labelCollection, image, attributeAsLabel, attributeAsImage);
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

  public equals(assetFamily: AssetFamily): boolean {
    return assetFamily.getIdentifier().equals(this.identifier);
  }

  public normalize(): NormalizedAssetFamily {
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

export const createAssetFamily = AssetFamilyImplementation.create;
export const denormalizeAssetFamily = AssetFamilyImplementation.createFromNormalized;
