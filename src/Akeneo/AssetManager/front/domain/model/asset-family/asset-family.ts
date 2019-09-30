import AssetFamilyIdentifier, {
  denormalizeAssetFamilyIdentifier,
  assetFamilyidentifiersAreEqual,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import LabelCollection, {
  NormalizedLabelCollection,
  createLabelCollection,
} from 'akeneoassetmanager/domain/model/label-collection';
import File, {NormalizedFile, denormalizeFile} from 'akeneoassetmanager/domain/model/file';
import AttributeIdentifier, {
  denormalizeAttributeIdentifier,
} from 'akeneoassetmanager/domain/model/attribute/identifier';

export interface NormalizedAssetFamily {
  identifier: string;
  code: string;
  labels: NormalizedLabelCollection;
  image: NormalizedFile;
  attribute_as_label: AttributeIdentifier;
  attribute_as_image: AttributeIdentifier;
}

export default interface AssetFamily {
  getIdentifier: () => AssetFamilyIdentifier;
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
    private identifier: AssetFamilyIdentifier,
    private labelCollection: LabelCollection,
    private image: File,
    private attributeAsLabel: AttributeIdentifier,
    private attributeAsImage: AttributeIdentifier
  ) {
    if (!(labelCollection instanceof LabelCollection)) {
      throw new InvalidArgumentError('AssetFamily expects a LabelCollection as labelCollection argument');
    }
    if (!(image instanceof File)) {
      throw new InvalidArgumentError('AssetFamily expects a File as image argument');
    }

    Object.freeze(this);
  }

  public static create(
    identifier: AssetFamilyIdentifier,
    labelCollection: LabelCollection,
    image: File,
    attributeAsLabel: AttributeIdentifier,
    attributeAsImage: AttributeIdentifier
  ): AssetFamily {
    return new AssetFamilyImplementation(identifier, labelCollection, image, attributeAsLabel, attributeAsImage);
  }

  public static createFromNormalized(normalizedAssetFamily: NormalizedAssetFamily): AssetFamily {
    const identifier = denormalizeAssetFamilyIdentifier(normalizedAssetFamily.identifier);
    const labelCollection = createLabelCollection(normalizedAssetFamily.labels);
    const image = denormalizeFile(normalizedAssetFamily.image);
    const attributeAsLabel = denormalizeAttributeIdentifier(normalizedAssetFamily.attribute_as_label);
    const attributeAsImage = denormalizeAttributeIdentifier(normalizedAssetFamily.attribute_as_image);

    return AssetFamilyImplementation.create(identifier, labelCollection, image, attributeAsLabel, attributeAsImage);
  }

  public getIdentifier(): AssetFamilyIdentifier {
    return this.identifier;
  }

  public getLabel(locale: string, fallbackOnCode: boolean = true) {
    if (!this.labelCollection.hasLabel(locale)) {
      return fallbackOnCode ? `[${this.getIdentifier()}]` : '';
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
    return assetFamilyidentifiersAreEqual(assetFamily.getIdentifier(), this.identifier);
  }

  public normalize(): NormalizedAssetFamily {
    return {
      identifier: this.getIdentifier(),
      code: this.getIdentifier(),
      labels: this.getLabelCollection().normalize(),
      image: this.getImage().normalize(),
      attribute_as_label: this.getAttributeAsLabel().normalize(),
      attribute_as_image: this.getAttributeAsImage().normalize(),
    };
  }
}

export const createAssetFamily = AssetFamilyImplementation.create;
export const denormalizeAssetFamily = AssetFamilyImplementation.createFromNormalized;
